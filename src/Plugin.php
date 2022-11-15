<?php
/**
 * Gatsby plugin for Craft CMS 3.x
 *
 * Plugin for enabling support for the Gatsby Craft CMS source plugin.
 *
 * @link      https://craftcms.com/
 * @copyright Copyright (c) 2020 Pixel & Tonic, Inc. <support@pixelandtonic.com>
 */

namespace craft\gatsbyhelper;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\elements\Entry;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlSchemaComponentsEvent;
use craft\events\RegisterPreviewTargetsEvent;
use craft\gatsbyhelper\gql\queries\Sourcing as SourcingDataQueries;
use craft\gatsbyhelper\models\Settings;
use craft\gatsbyhelper\services\Builds;
use craft\gatsbyhelper\services\Deltas;
use craft\gatsbyhelper\services\SourceNodes;
use craft\helpers\ElementHelper;
use craft\services\Gql;
use yii\base\Event;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 *
 * @method Settings getSettings()
 * @property-read Deltas $deltas
 * @property-read Settings $settings
 * @property-read SourceNodes $sourceNodes
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'builds' => ['class' => Builds::class],
                'deltas' => ['class' => Deltas::class],
                'sourceNodes' => ['class' => SourceNodes::class],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public string $schemaVersion = '2.0.0';

    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public string $minVersionRequired = '1.0.3';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->_registerGqlQueries();
        $this->_registerGqlComponents();
        $this->_registerElementListeners();
        $this->_registerLivePreviewListener();
    }

    /**
     * Return the SourceNodes service.
     *
     * @return SourceNodes
     * @throws \yii\base\InvalidConfigException
     */
    public function getSourceNodes(): SourceNodes
    {
        return $this->get('sourceNodes');
    }

    /**
     * Return the Deltas service.
     *
     * @return Deltas
     * @throws \yii\base\InvalidConfigException
     */
    public function getDeltas(): Deltas
    {
        return $this->get('deltas');
    }

    /**
     * Return the Builds service.
     *
     * @return Builds
     * @throws \yii\base\InvalidConfigException
     */
    public function getBuilds(): Builds
    {
        return $this->get('builds');
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): null|string
    {
        return Craft::$app->getView()->renderTemplate('gatsby-helper/settings', [
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Register the Gql queries
     */
    private function _registerGqlQueries(): void
    {
        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_QUERIES,
            static function(RegisterGqlQueriesEvent $event) {
                // Add my GraphQL queries
                $event->queries = array_merge(
                    $event->queries,
                    SourcingDataQueries::getQueries()
                );
            }
        );
    }

    /**
     * Register the Gql schema components
     */
    private function _registerGqlComponents(): void
    {
        Event::on(
            Gql::class,
            Gql::EVENT_REGISTER_GQL_SCHEMA_COMPONENTS,
            static function(RegisterGqlSchemaComponentsEvent $event) {
                $label = 'Gatsby';

                $event->queries[$label] = [
                    'gatsby:read' => ['label' => 'Allow discovery of sourcing data for Gatsby.'],
                ];
            }
        );
    }

    /**
     * Register the Element listeners
     */
    private function _registerElementListeners(): void
    {
        Event::on(
            Element::class,
            Element::EVENT_AFTER_DELETE,
            function(Event $event) {
                /** @var Element $element */
                $element = $event->sender;

                $this->getDeltas()->registerDeletedElement($element);

                /** @var Element $element */
                $element = $event->sender;
                $rootElement = ElementHelper::rootElement($element);

                // If the element or it's root element is a draft, don't trigger a build.
                if ($rootElement->getIsDraft() || $rootElement->getIsRevision()) {
                    return;
                }

                $this->getBuilds()->triggerBuild();
            }
        );

        Event::on(
            Element::class,
            Element::EVENT_AFTER_SAVE,
            function(Event $event) {
                /** @var Element $element */
                $element = $event->sender;
                $rootElement = ElementHelper::rootElement($element);

                // If the element or it's root element is a draft, don't trigger a build.
                if ($rootElement->getIsDraft() || $rootElement->getIsRevision()) {
                    return;
                }

                $this->getBuilds()->triggerBuild();
            }
        );
    }

    /**
     * Inject the live preview listener code.
     */
    private function _registerLivePreviewListener(): void
    {
        $previewWebhookUrl = Craft::parseEnv($this->getSettings()->previewWebhookUrl);

        if (!empty($previewWebhookUrl)) {
            Event::on(
                Entry::class,
                Entry::EVENT_REGISTER_PREVIEW_TARGETS,
                function(RegisterPreviewTargetsEvent $event) use ($previewWebhookUrl) {
                    /** @var Element $element */
                    $element = $event->sender;

                    $gqlTypeName = $element->getGqlTypeName();

                    $elementId = method_exists($element, 'getCanonicalId') ? $element->getCanonicalId() : $element->getSourceId();

                    $js = <<<JS
                        {
                            let currentlyPreviewing;

                            const alertGatsby = async function (event, doPreview) {
                                const url = doPreview ? event.previewTarget.url : '$previewWebhookUrl';
                                const compareUrl = new URL(url);

                                if (doPreview) {
                                    currentlyPreviewing = $elementId;
                                }

                                if (!currentlyPreviewing) {
                                    return;
                                }

                                const http = new XMLHttpRequest();

                                const payload = {
                                    operation: 'update',
                                    typeName: '$gqlTypeName',
                                    id: currentlyPreviewing,
                                    siteId: {$element->siteId}
                                };
                                
                                if (doPreview) {
                                    if(event.target.elementEditor) {
                                        payload.token = await event.target.elementEditor.getPreviewToken();
                                    } else {
                                        payload.token = await event.target.draftEditor.getPreviewToken();
                                    }
                                } else {
                                    currentlyPreviewing = null;
                                }
                                
                                http.open('POST', "$previewWebhookUrl", true);
                                http.setRequestHeader('Content-type', 'application/json');
                                http.setRequestHeader('x-preview-update-source', 'Craft CMS');
                                http.send(JSON.stringify(payload));
                            }

                            Garnish.on(Craft.Preview, 'beforeUpdateIframe', function(event) {
                                alertGatsby(event, true);
                            });
                            
                            Garnish.on(Craft.Preview, 'beforeClose', function(event) {
                                alertGatsby(event, false);
                            });

                            Garnish.\$win.on('beforeunload', function(event) {
                                alertGatsby(event, false);
                            });
                        }
JS;

                    Craft::$app->view->registerJs($js);
                });
        }
    }
}
