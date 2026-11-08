# Upgrading to Gatsby Helper 3.0 (Craft 6)

Gatsby Helper 3.0 is a native Craft 6 plugin. The GraphQL queries, the build and preview webhooks, and the stored data are unchanged, so gatsby-source-craft keeps working without changes. What changed is the PHP API that other plugins and modules use to extend Gatsby Helper.

## Requirements

- Craft CMS 6.0 or later
- PHP 8.5 or later
- `craftcms/yii2-adapter` is no longer needed

## Upgrade steps

1. Upgrade your project to Craft 6 first. Gatsby Helper 2.x runs on Craft 6 through the Yii 2 adapter, so you can do this before updating the plugin.
2. Update the plugin: `composer require craftcms/gatsby-helper:^3.0`.
3. Run `php artisan craft:up`. There are no migrations: the `gatsby_deletedelements` table and the plugin’s settings in project config are kept.
4. If your own code listens for Gatsby Helper’s events or calls its services, update it as described below.

## Breaking changes

### Events

The Yii events were replaced by Laravel events. Listeners receive the event only; there is no `sender`.

| Before | After |
| --- | --- |
| `SourceNodes::EVENT_REGISTER_SOURCE_NODE_TYPES` | `CraftCms\GatsbyHelper\Events\SourceNodeTypesResolving` |
| `Deltas::EVENT_REGISTER_IGNORED_TYPES` | `CraftCms\GatsbyHelper\Events\IgnoredTypesResolving` |

Before:

```php
use craft\gatsbyhelper\events\RegisterSourceNodeTypesEvent;
use craft\gatsbyhelper\services\SourceNodes;
use yii\base\Event;

Event::on(SourceNodes::class, SourceNodes::EVENT_REGISTER_SOURCE_NODE_TYPES, function (RegisterSourceNodeTypesEvent $event) {
    $event->types[BookInterface::getName()] = [
        'node' => 'book',
        'list' => 'books',
        'filterArgument' => 'type',
        'filterTypeExpression' => '(.+)_Book',
        'targetInterface' => BookInterface::getName(),
    ];
});
```

After:

```php
use CraftCms\GatsbyHelper\Events\SourceNodeTypesResolving;
use Illuminate\Support\Facades\Event;

Event::listen(function (SourceNodeTypesResolving $event): void {
    $event->types[BookInterface::getName()] = [
        'node' => 'book',
        'list' => 'books',
        'filterArgument' => 'type',
        'filterTypeExpression' => '(.+)_Book',
        'targetInterface' => BookInterface::getName(),
    ];
});
```

`IgnoredTypesResolving` works the same way, with a `types` list of element class names:

```php
use CraftCms\GatsbyHelper\Events\IgnoredTypesResolving;
use Illuminate\Support\Facades\Event;

Event::listen(function (IgnoredTypesResolving $event): void {
    $event->types[] = MyElement::class;
});
```

### Ignored element types

Element types are now listed by their Craft 6 class names, such as `CraftCms\Cms\Entry\Elements\Entry`. Matrix blocks no longer exist; `CraftCms\Cms\Field\Elements\ContentBlock` (Content Block fields’ nested elements) is ignored by default instead.

### Source node types

Craft 6 has no categories, tags or global sets, so `sourceNodeInformation` only lists entries, assets and users, depending on what the schema can query. Elements from plugins can still be added with `SourceNodeTypesResolving`.

### Classes and namespaces

The namespace changed from `craft\gatsbyhelper` to `CraftCms\GatsbyHelper`, and classes moved into Laravel-style folders.

| Before | After |
| --- | --- |
| `craft\gatsbyhelper\Plugin` | `CraftCms\GatsbyHelper\Plugin` |
| `craft\gatsbyhelper\models\Settings` | `CraftCms\GatsbyHelper\Settings` |
| `craft\gatsbyhelper\services\Builds` | `CraftCms\GatsbyHelper\Builds` |
| `craft\gatsbyhelper\services\Deltas` | `CraftCms\GatsbyHelper\Deltas` |
| `craft\gatsbyhelper\services\SourceNodes` | `CraftCms\GatsbyHelper\SourceNodes` |
| `craft\gatsbyhelper\events\RegisterSourceNodeTypesEvent` | `CraftCms\GatsbyHelper\Events\SourceNodeTypesResolving` |
| `craft\gatsbyhelper\events\RegisterIgnoredTypesEvent` | `CraftCms\GatsbyHelper\Events\IgnoredTypesResolving` |
| `craft\gatsbyhelper\gql\queries\Sourcing` | `CraftCms\GatsbyHelper\Gql\Queries\Sourcing` |
| `craft\gatsbyhelper\gql\resolvers\*` | `CraftCms\GatsbyHelper\Gql\Resolvers\*` |
| `craft\gatsbyhelper\gql\types\*` | `CraftCms\GatsbyHelper\Gql\Types\*` |
| `craft\gatsbyhelper\db\Table::DELETED_ELEMENTS` | `CraftCms\GatsbyHelper\Deltas::TABLE` |
| `craft\gatsbyhelper\helpers\Gql` | Removed. Use `CraftCms\Cms\Gql\GqlHelper::canSchema('gatsby')`. |

### Services

Services are container singletons instead of plugin components. Resolve or inject them instead of calling `Plugin::getInstance()->getBuilds()` and the like:

| Before | After |
| --- | --- |
| `Plugin::getInstance()->getBuilds()->triggerBuild()` | `app(Builds::class)->trigger()` |
| `Plugin::getInstance()->getDeltas()->getLastContentUpdateTime()` | `app(Deltas::class)->lastContentUpdateTime()` |
| `getDeltas()->getUpdatedNodesSinceTimeStamp($since, $siteIds)` | `app(Deltas::class)->updatedSince($since, $siteIds)` |
| `getDeltas()->getDeletedNodesSinceTimeStamp($since)` | `app(Deltas::class)->deletedSince($since)` |
| `getDeltas()->registerDeletedElement($element)` | `app(Deltas::class)->recordDeletion($element)` |
| `Plugin::getInstance()->getSourceNodes()->getSourceNodeTypes()` | `app(SourceNodes::class)->types()` |

Configuring the `builds`, `deltas` and `sourceNodes` components through `pluginConfigs` is no longer supported. Bind your own implementation in the container instead.

### Settings and config

The settings are unchanged and stay in project config. They can now also be set in `config/craft/gatsby-helper.php`, which overrides the settings page; publish a commented stub with `php artisan vendor:publish --tag=gatsby-helper`. The deprecated `previewServerUrl` setting was removed.

### Build webhook

Builds are sent with Laravel’s HTTP client once the request, console command or queue job finishes, so saving elements from the console or a queue worker now triggers a build. Several saves in one request still send one build. A failing Build Webhook URL is logged instead of thrown.

### Live Preview

Live Preview still posts to the Preview Webhook URL from the browser, with the same payload. The script is now a bundle published to `public/vendor/craftcms/gatsby-helper`, and it gets the payload from the new `gatsby-helper/preview-payload` action, which requires a control panel user.
