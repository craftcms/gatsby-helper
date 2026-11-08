<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper;

use CraftCms\Cms\Cp\SelectOptions;
use CraftCms\Cms\Element\Events\ElementDeleted;
use CraftCms\Cms\Element\Events\ElementSaved;
use CraftCms\Cms\Gql\Events\GqlSchemaComponentsResolving;
use CraftCms\Cms\Plugin\Plugin as BasePlugin;
use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Ui\Controls\Text;
use CraftCms\Cms\Ui\Nodes\Field;
use CraftCms\Cms\Ui\Ui;
use CraftCms\Cms\Ui\UiContext;
use CraftCms\GatsbyHelper\Gql\Queries\Sourcing;
use CraftCms\GatsbyHelper\Listeners\RecordDeletion;
use CraftCms\GatsbyHelper\Listeners\RegisterSchemaComponent;
use CraftCms\GatsbyHelper\Listeners\TriggerBuild;
use Override;

use function CraftCms\Cms\t;

/**
 * @method Settings getSettings()
 *
 * @since 1.0.0
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '2.0.0';

    public string $minVersionRequired = '2.0.0';

    public bool $hasCpSettings = true;

    /** @var array<class-string, class-string|class-string[]> */
    protected array $events = [
        ElementSaved::class => TriggerBuild::class,
        ElementDeleted::class => RecordDeletion::class,
        GqlSchemaComponentsResolving::class => RegisterSchemaComponent::class,
    ];

    protected array $gqlQueries = [
        Sourcing::class,
    ];

    /**
     * Tells Gatsby about Live Preview changes.
     */
    protected array $vite = [
        'resources/js/live-preview.js',
    ];

    public function boot(): void
    {
        $this->app->singleton(Settings::class, fn () => $this->getSettings());
    }

    #[Override]
    protected static function createSettings(): ?PluginSettings
    {
        return new Settings;
    }

    #[Override]
    public function settingsUi(UiContext $context = new UiContext): ?Ui
    {
        return Ui::make([
            Field::make(t('Preview Webhook URL', category: 'gatsby-helper'), Text::make('previewWebhookUrl')
                ->textExpanderTriggers(SelectOptions::getEnvTextExpanderTriggers(true)))
                ->instructions(t('The Gatsby webhook URL that should be called when content changes in Live Preview.', category: 'gatsby-helper')),
            Field::make(t('Build Webhook URL', category: 'gatsby-helper'), Text::make('buildWebhookUrl')
                ->textExpanderTriggers(SelectOptions::getEnvTextExpanderTriggers(true)))
                ->instructions(t('The Gatsby webhook URL that should be called when an element is saved or deleted.', category: 'gatsby-helper')),
        ]);
    }
}
