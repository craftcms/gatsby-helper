# Gatsby Helper Plugin for Craft CMS

Craft plugin that enables support for the [Craft Gatsby source plugin](https://github.com/craftcms/gatsby-source-craft).

## Requirements

This plugin requires Craft CMS 3.5.11 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Navigate to the Plugin Store in your project’s control panel and search for “Gatsby”. Then choose **Install** from in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/gatsby-helper

# tell Craft to install the plugin
./craft install/plugin gatsby-helper
```

## Configuration

To enable support for the Craft CMS source plugin for Gatsby, you’ll need a few things:

- Content in your Craft project that can be queried.
- A configured GraphQL endpoint for Craft.
- A GraphQL schema that has the Gatsby component enabled.

For configuring Craft’s GraphQL endpoint, schema, and tokens, see https://craftcms.com/docs/3.x/graphql.html#getting-started.

Once your endpoint and schema is established, be sure to enable **Allow discovery of sourcing data for Gatsby** in the **Gatsby** section of the schema you’d like to query with the [gatsby-source-craft](https://github.com/craftcms/gatsby-source-craft) Gatsby plugin.

You may optionally designate a **Preview Server URL** in the plugin settings.

## Integrating Third-Party Content

By default, this plugin only exposes elements provided by Craft itself. You can use your own custom module or third-party plugin to add elements using the following events:

### `registerSourceNodeTypes`

Event that’s triggered when registering source node types.

Plugins get a chance to specify additional elements that should be Gatsby source nodes.

```php
use craft\gatsbyhelper\events\RegisterSourceNodeTypesEvent;
use craft\gatsbyhelper\services\SourceNodes;
use yii\base\Event;

Event::on(
    SourceNodes::class,
    SourceNodes::EVENT_REGISTER_SOURCE_NODE_TYPES,
    function(RegisterSourceNodeTypesEvent $event) {
        $event->types[] = [
            'node' => 'book',
            'list' => 'books',
            'filterArgument' => 'type',
            'filterTypeExpression' => '(.+)_Book',
            'targetInterface' => BookInterface::getName(),
        ];
    }
);
```

Defining source node types looks a bit complex, so let’s go over the definition line-by-line:

- `node` is the GraphQL query Gatsby should use when querying for a single node. This must match the query name provided by your plugin.
- `list` is the GraphQL query Gatsby should use when querying for a list of nodes. This must match the query name provided by your plugin.
- `filterArgument` is the argument name to be used when querying for distinct types.\
When querying for assets, for example, it would be `volume` where entries would be `type`. This is necessary for elements that have different types. If your element does not, you can leave this blank.
- `filterTypeExpression` is used with `filterArgument` to figure out the value for the argument. The value of this will be applied as a regular expression to the GraphQL type name of a specific element and the first returned match will be used as the value.\
For example, for assets the value is `(.+)_Asset$` while for entries the value is `(?:.+)_(.+)_Entry+$` (because we need to use the entry type handle, not section handle).
- `targetInterface` is the GraphQL interface name to which the node type configuration should be applied.

### `registerIgnoredTypes`

Event that’s triggered when registering ignored element types.

Plugins get a chance to specify which element types should not be updated individually.

```php
use craft\gatsbyhelper\events\RegisterIgnoredTypesEvent;
use craft\gatsbyhelper\services\Deltas;
use yii\base\Event;

Event::on(
    Deltas::class,
    Deltas::EVENT_REGISTER_IGNORED_TYPES,
    function(RegisterIgnoredTypesEvent $event) {
        $event->types[] = MyType::class;
    }
);
```

This event should be used for element types that will _always_ be updated as part of a different element. For example, a Matrix block will never be updated by itself—it will always be updated when saving some other element, so the changes to individual Matrix blocks should not be tracked.

## Resources

We highly recommend you check out these resources as you’re getting started with Craft CMS and Gatsby:

- **[Craft GraphQL Docs](https://craftcms.com/docs/3.x/graphql.html)** - official documentation for GraphQL setup and usage.
- **[Intro to Craft CMS Tutorial](https://craftcms.com/docs/getting-started-tutorial/)** - best place to start if you’re new to Craft CMS.
- **[Gatsby Tutorials](https://www.gatsbyjs.com/tutorial/)** - best place to start if you’re new to Gatsby.
- **[Craft Discord](https://craftcms.com/discord)** – one of the most friendly and helpful Discords on the planet.
- **[Craft Stack Exchange](http://craftcms.stackexchange.com/)** – community-run Q&A for Craft developers.

