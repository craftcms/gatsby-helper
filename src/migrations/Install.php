<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\gatsbyhelper\migrations;

use craft\db\Migration;
use craft\gatsbyhelper\db\Table;

/**
 * Installation Migration
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class Install extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Table::DELETED_ELEMENTS, [
            'elementId' => $this->primaryKey(),
            'typeName' => $this->string()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Table::DELETED_ELEMENTS);

        return true;
    }
}
