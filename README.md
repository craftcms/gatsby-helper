<p align="center"><img src="./resources/icon.svg" width="100" height="100" alt="Gatsby logo"></p>

<h1 align="center">Gatsby Helper</h1>

This plugin enables support for the [gatsby-source-craft](https://github.com/craftcms/gatsby-source-craft) Gatsby source plugin. Combined, they provide an integration between Craft CMS and [Gatsby](https://www.gatsbyjs.com/).

## Requirements

This plugin requires Craft CMS 6.0 or later and PHP 8.5 or later. For Craft 4 and 5, use Gatsby Helper 2.x.

Upgrading from 2.x? See [UPGRADE.md](UPGRADE.md).

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Navigate to the Plugin Store in your project’s control panel and search for “Gatsby Helper”. Then choose **Install** from in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/gatsby-helper

# tell Craft to install the plugin
php artisan craft:plugin:install gatsby-helper
```

## Configuration

Set the **Preview Webhook URL** and **Build Webhook URL** in **Settings → Plugins → Gatsby Helper**. Both accept environment variables, such as `$GATSBY_BUILD_WEBHOOK_URL`.

To manage them in code instead, publish the config file and set the values there. They override the settings page:

```bash
php artisan vendor:publish --tag=gatsby-helper
```

```php
// config/craft/gatsby-helper.php
return [
    'previewWebhookUrl' => env('GATSBY_PREVIEW_WEBHOOK_URL', ''),
    'buildWebhookUrl' => env('GATSBY_BUILD_WEBHOOK_URL', ''),
];
```

- **Build Webhook URL:** called with a `POST` once the request, console command or queue job that saved or deleted an element finishes. Drafts and revisions don’t trigger builds.
- **Preview Webhook URL:** called from the browser while Live Preview is open, with the element and a preview token, and again when it closes.

Give the GraphQL schema that Gatsby uses the **Allow discovery of sourcing data for Gatsby** (`gatsby:read`) permission, along with read access to the content it sources.

## Extending

Other plugins and modules can add source node types or exclude element types from change tracking:

```php
use CraftCms\GatsbyHelper\Events\IgnoredTypesResolving;
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

Event::listen(function (IgnoredTypesResolving $event): void {
    $event->types[] = MyElement::class;
});
```

See the `gatsby-source-craft` documentation for more on configuring the plugin with your Gatsby project: https://github.com/craftcms/gatsby-source-craft
