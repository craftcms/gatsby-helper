<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\gatsby\migrations;

use Craft;
use craft\awss3\Volume;
use craft\db\Migration;
use craft\gatsby\db\Table;
use craft\helpers\Json;
use craft\services\Volumes;

/**
 * Installation Migration
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
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
