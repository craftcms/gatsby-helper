<?php

namespace craft\gatsbyhelper\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

/**
 * m201116_123000_settingname migration.
 */
class m201116_123000_settingname extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $projectConfig = Craft::$app->getProjectConfig();

        $schemaVersion = $projectConfig->get('plugins.gatsby-helper.schemaVersion', true);
        $projectConfig->muteEvents = true;

        if (version_compare($schemaVersion, '1.0.1', '<')) {
            $settings = $projectConfig->get('plugins.gatsby-helper.settings') ?? [];
            if (array_key_exists('previewServerUrl', $settings)) {
                $url = ArrayHelper::remove($settings, 'previewServerUrl');
                // If it's not set to an environment variable or alias, add /__refresh to it
                if (isset($url[0]) && !in_array($url[0], ['$', '@'])) {
                    $url = StringHelper::ensureRight($url, '/__refresh');
                }
                $settings['previewWebhookUrl'] = $url;
                $projectConfig->set('plugins.gatsby-helper.settings', $settings, '[Gatsby helper] Migrating previewServerUrl setting.');
            }
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
