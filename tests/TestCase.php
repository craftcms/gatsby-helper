<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Tests;

use CraftCms\Cms\Plugin\Testing\PluginTestCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

abstract class TestCase extends PluginTestCase
{
    public const string BUILD_URL = 'https://gatsby.test/__build';

    public const string PREVIEW_URL = 'https://gatsby.test/__refresh';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Use Craft’s model factories, as core’s tests do
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'CraftCms\\Cms\\Database\\Factories\\'.class_basename($modelName).'Factory',
        );
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('craft.gatsby-helper', [
            'buildWebhookUrl' => self::BUILD_URL,
            'previewWebhookUrl' => self::PREVIEW_URL,
        ]);
    }
}
