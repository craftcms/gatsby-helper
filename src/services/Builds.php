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
use craft\gatsbyhelper\Plugin;
use craft\helpers\App;
use yii\base\Application;

/**
 * Builds Service
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 *
 * @property-read null|string|false $lastContentUpdateTime
 * @property-read string $version
 */
class Builds extends Component
{
    private bool $_buildQueued = false;

    /**
     * Trigger a Gatsby build.
     */
    public function triggerBuild(): void
    {
        $buildWebhookUrl = App::parseEnv(Plugin::getInstance()->getSettings()->buildWebhookUrl);

        if (!empty($buildWebhookUrl) && $this->_buildQueued === false) {
            $this->_buildQueued = true;
            Craft::$app->on(Application::EVENT_AFTER_REQUEST, function() use ($buildWebhookUrl) {
                $guzzle = Craft::createGuzzleClient([
                    'headers' => [
                        'x-preview-update-source' => 'Craft CMS',
                        'Content-type' => 'application/json'
                    ]
                ]);
                $guzzle->request('POST', $buildWebhookUrl);
            }, null, false);
        }
    }
}
