<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Gql\Resolvers;

use CraftCms\Cms\Gql\Resolvers\Resolver;
use CraftCms\GatsbyHelper\SourceNodes;
use GraphQL\Type\Definition\ResolveInfo;

/** @since 1.0.0 */
class SourceNode extends Resolver
{
    /** @param array<string, mixed> $arguments */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): array
    {
        return array_values(app(SourceNodes::class)->types());
    }
}
