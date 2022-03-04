<?php
/**
 * Gatsby plugin for Craft CMS 3.x
 *
 * Plugin for enabling support for the Gatsby Craft CMS source plugin.
 *
 * @link      https://craftcms.com/
 * @copyright Copyright (c) 2020 Pixel & Tonic, Inc. <support@pixelandtonic.com>
 */

namespace craft\gatsbyhelper\services;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\db\Query;
use craft\db\Table as CraftTable;
use craft\elements\MatrixBlock;
use craft\gatsbyhelper\db\Table;
use craft\gatsbyhelper\events\RegisterIgnoredTypesEvent;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use DateTime;
use yii\db\Expression;

/**
 * Deltas Service
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 *
 * @property-read null|string|false $lastContentUpdateTime
 * @property-read string $version
 */
class Deltas extends Component
{
    /**
     * @event RegisterIgnoredTypesEvent The event that is triggered when registering ignored element types.
     *
     * Plugins get a chance to specify which element types should not be updated individually.
     *
     * ---
     * ```php
     * use craft\events\RegisterIgnoredTypesEvent;
     * use craft\gatsbyhelper\services\Deltas;
     * use yii\base\Event;
     *
     * Event::on(Deltas::class, Deltas::EVENT_REGISTER_IGNORED_TYPES, function(RegisterIgnoredTypesEvent $event) {
     *     $event->types[] = MyType::class;
     * });
     * ```
     */
    const EVENT_REGISTER_IGNORED_TYPES = 'registerIgnoredTypes';

    /**
     * Get the last time content was updated or deleted.
     *
     * @return false|string|null
     * @throws \Exception
     */
    public function getLastContentUpdateTime()
    {
        $lastUpdated = (new Query())
            ->select(new Expression('MAX([[dateUpdated]])'))
            ->from([CraftTable::ELEMENTS])
            ->where(['dateDeleted' => null])
            ->scalar();

        $lastDeleted = (new Query())
            ->select(new Expression('MAX([[dateDeleted]])'))
            ->from([Table::DELETED_ELEMENTS])
            ->scalar();

        if (!$lastDeleted) {
            return $lastUpdated;
        }

        if (!$lastUpdated) {
            return $lastDeleted;
        }

        if (DateTimeHelper::toDateTime($lastUpdated) < DateTimeHelper::toDateTime($lastDeleted)) {
            return $lastDeleted;
        }

        return $lastUpdated;
    }

    /**
     * Get updated nodes since a specific timestamp.
     *
     * @param string $timestamp
     * @param int[] $siteIds
     * @return array
     */
    public function getUpdatedNodesSinceTimeStamp(string $timestamp, array $siteIds): array
    {
        $structureUpdates = (new Query())
            ->select(['elementId', 'structureId'])
            ->from([CraftTable::STRUCTUREELEMENTS])
            ->andWhere(['>', 'dateUpdated', Db::prepareDateForDb($timestamp)])
            ->pairs();

        return (new Query())
            ->select(['e.id', 'e.type', 'es.siteId'])
            ->from(['e' => CraftTable::ELEMENTS])
            ->innerJoin(['es' => CraftTable::ELEMENTS_SITES], ['and', '[[e.id]] = [[es.elementId]]', ['es.siteId' => $siteIds]])
            ->where(['e.dateDeleted' => null])
            ->andWhere(['not in', 'e.type', $this->_getIgnoredTypes()])
            ->andWhere(
                [
                    'or',
                    ['>', 'e.dateUpdated', Db::prepareDateForDb($timestamp)],
                    ['IN', 'e.id', array_keys($structureUpdates)]
                ]
            )
            ->andWhere(['e.revisionId' => null])
            ->andWhere(['e.draftId' => null])
            ->all();
    }

    /**
     * Get deleted nodes since a specific timestamp.
     *
     * @param string $timestamp
     * @return array
     */
    public function getDeletedNodesSinceTimeStamp(string $timestamp): array
    {
        return (new Query())
            ->select(['elementId', 'siteId', 'typeName'])
            ->from([Table::DELETED_ELEMENTS])
            ->where(['>', 'dateDeleted', Db::prepareDateForDb($timestamp)])
            ->all();
    }

    /**
     * Register a deleted element.
     *
     * @param Element $element
     * @return bool
     */
    public function registerDeletedElement(Element $element): bool
    {
        if (in_array(get_class($element), $this->_getIgnoredTypes(), true)) {
            return false;
        }

        // In case this is being deleted _again_
        Db::delete(Table::DELETED_ELEMENTS, ['elementId' => $element->id]);

        $sites = Craft::$app->getSites()->getAllSiteIds(true);

        $rows = [];
        foreach ($sites as $site) {
            $rows[] = [$element->id, $site, $element->getGqlTypeName(), Db::prepareDateForDb(new DateTime())];
        }

        return (bool)Db::batchInsert(Table::DELETED_ELEMENTS, ['elementId', 'siteId', 'typeName', 'dateDeleted'], $rows, false);
    }

    /**
     * Return a list of all the element types that should be ignored.
     *
     * @return string[]
     */
    private function _getIgnoredTypes()
    {
        $event = new RegisterIgnoredTypesEvent([
            'types' => [
                MatrixBlock::class
            ],
        ]);

        $this->trigger(self::EVENT_REGISTER_IGNORED_TYPES, $event);

        return $event->types;
    }
}
