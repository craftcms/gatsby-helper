<?php
/**
 * Gatsby plugin for Craft CMS 3.x
 *
 * Plugin for enabling support for the Gatsby Craft CMS source plugin.
 *
 * @link      https://craftcms.com/
 * @copyright Copyright (c) 2020 Pixel & Tonic, Inc. <support@pixelandtonic.com>
 */

namespace craft\gatsby;

use craft\base\Element;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlSchemaComponentsEvent;
use craft\gatsby\gql\queries\Sourcing as SourcingDataQueries;
use craft\gatsby\services\Deltas;
use craft\gatsby\services\SourceNodes;
use craft\services\Gql;
use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 *
 * @property  SourceNodes $data
 */
class Plugin extends \craft\base\Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Gatsby
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->_registerServices();
        $this->_registerGqlQueries();
        $this->_registerGqlComponents();
        $this->_registerElementListeners();
    }

    /**
     * Return the SrouceNodes service.
     * @return SourceNodes
     * @throws \yii\base\InvalidConfigException
     */
    public function getSourceNodes(): SourceNodes
    {
        return $this->get('sourceNodes');
    }

    /**
     * Return the Deltas service.
     * @return SourceNodes
     * @throws \yii\base\InvalidConfigException
     */
    public function getDeltas(): Deltas
    {
        return $this->get('deltas');
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
     * Register the Gql permissions
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
     * Register the Gql permissions
     */
    private function _registerElementListeners()
    {
        Event::on(Element::class, Element::EVENT_AFTER_DELETE, function(Event $event) {
            /** @var Element $element */
            $element = $event->sender;

            $this->getDeltas()->registerDeletedElement($element);
        });
    }

    /**
     * Register the services
     */
    public function _registerServices()
    {
        $this->setComponents([
            'sourceNodes' => [
                'class' => SourceNodes::class,
            ],
            'deltas' => [
                'class' => Deltas::class,
            ],
        ]);
    }
}
