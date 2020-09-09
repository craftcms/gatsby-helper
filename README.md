# Gatsby plugin for Craft CMS 3

Plugin for enabling support for the Gatsby Craft CMS source plugin.

It requires for the corresponding [Craft Gatsby source plugin](https://github.com/craftcms/gatsby-source-craftcms) to be installed on the Craft site.

## Requirements

This plugin requires Craft CMS 3.5.9 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require craftcms/craft-gatsby

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Gatsby.

## Configuring Gatsby

To enable support for the Craft CMS source plugin for Gatsby, you must create a GraphQL schema, that, in addition to all the content you wish to source to your Gatsby site, also has the `Gatsby` component enabled.

## Integrating 3rd party content

By default this plugin only exposes elements provided by Craft itself. It's possible for 3rd party plugin developers to add more elements by using the following events:

### `registerSourceNodeTypes`

The event that is triggered when registering source node types.

Plugins get a chance to specify additional elements that should be Gatsby source nodes.
 
```php
use craft\events\RegisterSourceNodeTypesEvent;
use craft\gatsby\services\SourceNodes;
use yii\base\Event;

Event::on(SourceNodes::class, SourceNodes::EVENT_REGISTER_SOURCE_NODE_TYPES, function(RegisterSourceNodeTypesEvent $event) {
    $event->types[] = [
        'node' => 'book',
        'list' => 'books',
        'filterArgument' => 'type',
        'filterTypeExpression' => '(.+)_Book',
        'targetInterface' => BookInterface::getName(),
    ];
});
```

Defining source node types looks a bit complex, so let's go over the definition line-by-line:
* `node` - this is the GraphQL query that Gatsby should use when querying for a single node. This must match the query name provided by your plugin.
* `list` - this is the GraphQL query that Gatsby should use when querying for a list of nodes. This must match the query name provided by your plugin.
* `filterArgument` - This is the argument name to be used when querying for distinct types. E.g. when querying for assets, it would be `volume`, but when querying for entries, it would be `type`. This is needed for elements that have different types. If your element does not, you can leave this blank.
* `filterTypeExpression` - This is used together with `filterArgument` to figure out the value for the argument. The value of this will be applied as a RegExp to the GraphQL type name of a specific element and the first returned match will be used as the value. For example, for assets the value is `(.+)_Asset$` while for entries the value is `(?:.+)_(.+)_Entry+$` (because we need to use the entry type handle, not section handle).
* `targetInterface` - this is the GraphQL interface name to which the node type configuration should be applied to.

### `registerIgnoredTypes`

The event that is triggered when registering ignored element types.

Plugins get a chance to specify which element types should not be updated individually.

```php
use craft\events\RegisterIgnoredTypesEvent;
use craft\gatsby\services\Deltas;
use yii\base\Event;

Event::on(Deltas::class, Deltas::EVENT_REGISTER_IGNORED_TYPES, function(RegisterIgnoredTypesEvent $event) {
    $event->types[] = MyType::class;
});
```

This event should be used for element types that will _always_ be updated as part of a different element. For example, a Matrix block will never be updated by itself - it will always be updated when saving some other element, so the changes to individual Matrix blocks should not be tracked.
