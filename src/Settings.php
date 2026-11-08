<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper;

use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Support\Env;

/**
 * Settings, saved from the plugin’s settings page or overridden in `config/craft/gatsby-helper.php`.
 *
 * @since 1.0.0
 */
class Settings extends PluginSettings
{
    /**
     * @var string The Gatsby webhook URL that Live Preview calls when content changes. Can be an environment variable or alias.
     */
    public string $previewWebhookUrl = '';

    /**
     * @var string The Gatsby webhook URL that’s called when an element is saved or deleted. Can be an environment variable or alias.
     */
    public string $buildWebhookUrl = '';

    /**
     * Returns the Preview Webhook URL with any environment variable or alias resolved.
     */
    public function previewWebhookUrl(): ?string
    {
        return Env::parse($this->previewWebhookUrl) ?: null;
    }

    /**
     * Returns the Build Webhook URL with any environment variable or alias resolved.
     */
    public function buildWebhookUrl(): ?string
    {
        return Env::parse($this->buildWebhookUrl) ?: null;
    }
}
