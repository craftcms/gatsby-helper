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
use craft\elements\Entry;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlSchemaComponentsEvent;
use craft\events\RegisterPreviewTargetsEvent;
use craft\gatsbyhelper\gql\queries\Sourcing as SourcingDataQueries;
use craft\gatsbyhelper\models\Settings;
use craft\gatsbyhelper\services\Builds;
use craft\gatsbyhelper\services\Deltas;
use craft\gatsbyhelper\services\SourceNodes;
use craft\helpers\StringHelper;
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
    public $schemaVersion = '2.0.0';

    /**
     * @inheritdoc
     */
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_registerServices();
        $this->_registerGqlQueries();
        $this->_registerGqlComponents();
        $this->_registerElementListeners();
        $this->_registerLivePreviewListener();
    }

    /**
     * Return the SourceNodes service.
     *
     * @return SourceNodes
     */
    public function getSourceNodes(): SourceNodes
    {
        return $this->get('sourceNodes');
    }

    /**
     * Return the Deltas service.
     *
     * @return Deltas
     */
    public function getDeltas(): Deltas
    {
        return $this->get('deltas');
    }

    /**
     * Return the Builds service.
     *
     * @return Deltas
     */
    public function getBuilds(): Builds
    {
        return $this->get('builds');
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('gatsby-helper/settings', [
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Register the Gql queries
     */
    private function _registerGqlQueries()
    {
        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_QUERIES, function(RegisterGqlQueriesEvent $event) {
            // Add my GraphQL queries
            $event->queries = array_merge(
                $event->queries,
                SourcingDataQueries::getQueries()
            );
        });
    }

    /**
     * Register the Gql schema components
     */
    private function _registerGqlComponents()
    {
        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_SCHEMA_COMPONENTS, function(RegisterGqlSchemaComponentsEvent $event) {
            $label = 'Gatsby';

            $event->queries[$label] = [
                'gatsby:read' => ['label' => 'Allow discovery of sourcing data for Gatsby.']
            ];
        });
    }

    /**
     * Register the Element listeners
     */
    private function _registerElementListeners()
    {
        Event::on(Element::class, Element::EVENT_AFTER_DELETE, function(Event $event) {
            /** @var Element $element */
            $element = $event->sender;

            $this->getDeltas()->registerDeletedElement($element);
        });

        Event::on(Element::class, Element::EVENT_AFTER_SAVE, function(Event $event) {
            /** @var Element $element */
            $element = $event->sender;

            $this->getBuilds()->triggerBuild();
        });
    }

    /**
     * Inject the live preview listener code.
     */
    private function _registerLivePreviewListener()
    {
        $previewWebhookUrl = Craft::parseEnv($this->getSettings()->previewWebhookUrl);

        if (!empty($previewWebhookUrl)) {
            Event::on(Entry::class, Entry::EVENT_REGISTER_PREVIEW_TARGETS, function(RegisterPreviewTargetsEvent $event) use ($previewWebhookUrl) {
                /** @var Element $element */
                $element = $event->sender;

                $gqlTypeName = $element->getGqlTypeName();
                $elementId = $element->getSourceId();

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
                                payload.token = await event.target.draftEditor.getPreviewToken();
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

    /**
     * Register the services
     */
    private function _registerServices()
    {
        $this->setComponents([
            'sourceNodes' => [
                'class' => SourceNodes::class,
            ],
            'deltas' => [
                'class' => Deltas::class,
            ],
            'builds' => [
                'class' => Builds::class,
            ],
        ]);
    }
}
