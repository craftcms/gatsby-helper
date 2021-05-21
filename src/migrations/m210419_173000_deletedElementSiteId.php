<?php

namespace craft\gatsbyhelper\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table as CraftTable;
use craft\gatsbyhelper\db\Table;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

/**
 * m210419_173000_deletedElementSiteId migration.
 */
class m210419_173000_deletedElementSiteId extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Get the current deleted elements
        $deletedElements = (new Query())->select(['elementId', 'typeName', 'dateDeleted'])->from([Table::DELETED_ELEMENTS])->all();

        // Get rid of the Primary Key
        $this->alterColumn(Table::DELETED_ELEMENTS, 'elementId', $this->integer());
        $this->dropPrimaryKey('elementId', Table::DELETED_ELEMENTS);

        // Drop the entries
        $this->truncateTable(Table::DELETED_ELEMENTS);

        // Add new columns and index
        $this->addColumn(Table::DELETED_ELEMENTS, 'id', $this->primaryKey()->first());
        $this->addColumn(Table::DELETED_ELEMENTS, 'siteId', $this->integer()->notNull()->after('elementId'));
        $this->createIndex(null, Table::DELETED_ELEMENTS, ['elementId', 'siteId'], true);

        // Re-add all the entries, but for each site.
        $sites = (new Query())->select(['id'])->from([CraftTable::SITES])->column();

        foreach ($deletedElements as $deletedElement) {
            $rows = [];

            foreach ($sites as $site) {
                $rows[] = [$deletedElement['elementId'], $site, $deletedElement['typeName'], $deletedElement['dateDeleted']];
            }

            $this->batchInsert(Table::DELETED_ELEMENTS, ['elementId', 'siteId', 'typeName', 'dateDeleted'], $rows, false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210419_173000_deletedElementSiteId cannot be reverted.\n";

        return false;
    }
}
