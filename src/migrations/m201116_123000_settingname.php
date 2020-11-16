<?php

namespace craft\gatsbyhelper\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;
use craft\helpers\Localization;
use craft\helpers\MigrationHelper;
use yii\base\InvalidArgumentException;

/**
 * m201116_123000_settingname migration.
 */
class m201116_123000_settingname extends Migration
{
    private $_usersTable;
    private $_prefsTable;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $projectConfig = Craft::$app->getProjectConfig();

        $schemaVersion = $projectConfig->get('plugins.gatsby-helper.schemaVersion', true);
        $projectConfig->muteEvents = true;

        if (version_compare($schemaVersion, '1.0.1', '<')) {
            $message = '[Gatsby helper] Migrating previewServerUrl setting.';
            $projectConfig->set('plugins.gatsby-helper.settings.webhookTarget', $projectConfig->get('plugins.gatsby-helper.settings.previewServerUrl'), $message);
            $projectConfig->remove('plugins.gatsby-helper.settings.previewServerUrl', $message);
        }

        $projectConfig->muteEvents = false;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m201116_123000_settingname cannot be reverted.\n";

        return false;
    }
}
