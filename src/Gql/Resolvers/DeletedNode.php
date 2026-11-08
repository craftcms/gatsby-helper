<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Gql\Resolvers;

use CraftCms\Cms\Gql\Resolvers\Resolver;
use CraftCms\GatsbyHelper\Deltas;
use GraphQL\Type\Definition\ResolveInfo;

/** @since 1.0.0 */
class DeletedNode extends Resolver
{
    /** @param array<string, mixed> $arguments */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): array
    {
        return array_map(fn (array $node) => [
            'nodeId' => $node['elementId'],
            'siteId' => $node['siteId'],
            'nodeType' => $node['typeName'],
        ], app(Deltas::class)->deletedSince($arguments['since']));
    }
}
