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
use GuzzleHttp\Exception\GuzzleException;
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

        if (empty($buildWebhookUrl)) {
            return;
        }

        // Craft 6 only triggers EVENT_AFTER_REQUEST for web requests. Laravel’s deferred callbacks also run
        // after console commands and after each queue job, and a named callback only runs once.
        if (class_exists(\CraftCms\Cms\Cms::class)) {
            /** @phpstan-ignore-next-line Laravel’s defer() only exists on Craft 6 */
            \Illuminate\Support\defer(fn() => $this->_sendBuildRequest($buildWebhookUrl), 'gatsby-helper:build');
            return;
        }

        if ($this->_buildQueued === false) {
            $this->_buildQueued = true;
            Craft::$app->on(Application::EVENT_AFTER_REQUEST, function() use ($buildWebhookUrl) {
                $this->_sendBuildRequest($buildWebhookUrl);
            }, null, false);
        }
    }

    private function _sendBuildRequest(string $buildWebhookUrl): void
    {
        $guzzle = Craft::createGuzzleClient([
            'headers' => [
                'x-preview-update-source' => 'Craft CMS',
                'Content-type' => 'application/json',
            ],
        ]);

        try {
            $guzzle->request('POST', $buildWebhookUrl);
        } catch (GuzzleException $e) {
            Craft::error("Unable to trigger a Gatsby build: {$e->getMessage()}", __METHOD__);
        }
    }
}
