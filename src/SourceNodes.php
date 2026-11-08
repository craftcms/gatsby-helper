<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper;

use CraftCms\Cms\Gql\GqlEntityRegistry;
use CraftCms\Cms\Gql\GqlHelper;
use CraftCms\Cms\Gql\Interfaces\Elements\Asset as AssetInterface;
use CraftCms\Cms\Gql\Interfaces\Elements\Entry as EntryInterface;
use CraftCms\Cms\Gql\Interfaces\Elements\User as UserInterface;
use CraftCms\GatsbyHelper\Events\SourceNodeTypesResolving;
use Illuminate\Container\Attributes\Singleton;

/**
 * The element types Gatsby sources, and the queries it uses for them.
 *
 * @since 1.0.0
 */
#[Singleton]
class SourceNodes
{
    /**
     * Returns the source node types the active GraphQL schema can query, indexed by interface name.
     *
     * @return array<string, array{node: string, list: string, filterArgument: string, filterTypeExpression: string, targetInterface: string}>
     */
    public function types(): array
    {
        $types = [];

        if (GqlHelper::canQueryEntries()) {
            $types[EntryInterface::getName()] = [
                'node' => 'entry',
                'list' => 'entries',
                'filterArgument' => 'type',
                'filterTypeExpression' => '(?:.+)_(.+)_Entry+$',
                'targetInterface' => EntryInterface::getName(),
            ];
        }

        if (GqlHelper::canQueryAssets()) {
            $types[AssetInterface::getName()] = [
                'node' => 'asset',
                'list' => 'assets',
                'filterArgument' => 'volume',
                'filterTypeExpression' => '(.+)_Asset$',
                'targetInterface' => AssetInterface::getName(),
            ];
        }

        if (GqlHelper::canQueryUsers()) {
            $types[UserInterface::getName()] = [
                'node' => 'user',
                'list' => 'users',
                'filterArgument' => '',
                'filterTypeExpression' => '',
                'targetInterface' => UserInterface::getName(),
            ];
        }

        event($event = new SourceNodeTypesResolving($types));

        return array_map(fn (array $type) => [
            ...$type,
            'targetInterface' => GqlEntityRegistry::prefixTypeName($type['targetInterface']),
        ], $event->types);
    }
}
