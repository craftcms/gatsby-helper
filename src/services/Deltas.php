<?php
/**
 * Gatsby plugin for Craft CMS 3.x
 *
 * Plugin for enabling support for the Gatsby Craft CMS source plugin.
 *
 * @link      https://craftcms.com/
 * @copyright Copyright (c) 2020 Pixel & Tonic, Inc. <support@pixelandtonic.com>
 */

namespace craft\gatsby\services;

use craft\base\Component;
use craft\base\Element;
use craft\db\Query;
use craft\db\Table as CraftTable;
use craft\elements\MatrixBlock;
use craft\gatsby\db\Table;
use craft\gatsby\events\RegisterIgnoredTypesEvent;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use yii\db\Expression;

/**
 * Deltas Service
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 *
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
     * use craft\gatsby\services\Deltas;
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
     * @return array
     * @throws \yii\base\Exception
     */
    public function getUpdatedNodesSinceTimeStamp(string $timestamp): array
    {
        return (new Query())
            ->select(['id', 'type'])
            ->from([CraftTable::ELEMENTS])
            ->where(['dateDeleted' => null])
            ->andWhere(['not in', 'type', $this->_getIgnoredTypes()])
            ->andWhere(['>', 'dateUpdated', Db::prepareDateForDb($timestamp)])
            ->andWhere(['revisionId' => null])
            ->andWhere(['draftId' => null])
            ->pairs();
    }

    /**
     * Get deleted nodes since a specific timestamp.
     *
     * @param string $timestamp
     * @return array
     * @throws \yii\base\Exception
     */
    public function getDeletedNodesSinceTimeStamp(string $timestamp): array
    {
        return (new Query())
            ->select(['elementId', 'typeName'])
            ->from([Table::DELETED_ELEMENTS])
            ->where(['>', 'dateDeleted', Db::prepareDateForDb($timestamp)])
            ->pairs();
    }

    /**
     * Register a deleted element.
     *
     * @param Element $element
     * @return bool
     * @throws \yii\db\Exception
     */
    public function registerDeletedElement(Element $element): bool
    {
        if (in_array(get_class($element), $this->_getIgnoredTypes(), true)) {
            return false;
        }

        // In case this is being deleted _again_
        Db::delete(Table::DELETED_ELEMENTS, ['elementId' => $element->id]);

        return (bool)Db::insert(Table::DELETED_ELEMENTS, [
            'elementId' => $element->id,
            'typeName' => $element->getGqlTypeName(),
            'dateDeleted' => Db::prepareDateForDb(new \DateTime())
        ], false);
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
