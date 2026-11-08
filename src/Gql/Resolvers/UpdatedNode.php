<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Gql\Resolvers;

use CraftCms\Cms\Gql\Exceptions\GqlException;
use CraftCms\Cms\Gql\GqlEntityRegistry;
use CraftCms\Cms\Gql\Interfaces\Element as ElementInterface;
use CraftCms\Cms\Gql\Resolvers\Resolver;
use CraftCms\Cms\Site\Sites;
use CraftCms\Cms\Support\Facades\Elements;
use CraftCms\Cms\Support\Facades\Gql;
use CraftCms\GatsbyHelper\Deltas;
use CraftCms\GatsbyHelper\SourceNodes;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Log;

/** @since 1.0.0 */
class UpdatedNode extends Resolver
{
    /** @param array<string, mixed> $arguments */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): array
    {
        $sites = app(Sites::class);
        $handles = $arguments['site'] ?? [] ?: [$sites->getPrimarySite()->handle];

        $siteIds = [];
        foreach ($handles as $handle) {
            if ($site = $sites->getSiteByHandle($handle, false)) {
                $siteIds[] = $site->id;
            }
        }

        // The interfaces Gatsby sources (already prefixed), plus the base element interface
        $allowedInterfaces = [
            ...array_keys(app(SourceNodes::class)->types()),
            GqlEntityRegistry::prefixTypeName(ElementInterface::getName()),
        ];

        $schema = Gql::getSchemaDef();
        $resolved = [];

        foreach (app(Deltas::class)->updatedSince($arguments['since'], $siteIds) as $node) {
            $element = Elements::getElementById($node['id'], $node['type']);

            // Skip elements that are gone, or that don't have a GraphQL type of their own
            if ($element === null || $element->getGqlTypeName() === 'Element') {
                continue;
            }

            // Skip types the schema doesn't include (https://github.com/craftcms/gatsby-helper/issues/31)
            try {
                $gqlType = $schema->getType(GqlEntityRegistry::prefixTypeName($element->getGqlTypeName()));
            } catch (GqlException $e) {
                Log::error($e->getMessage());

                continue;
            }

            if (! $gqlType instanceof ObjectType) {
                continue;
            }

            // Make sure Gatsby can handle updates to these elements
            foreach ($gqlType->getInterfaces() as $interface) {
                if (! in_array($interface->name, $allowedInterfaces, true)) {
                    continue 2;
                }
            }

            $resolved[] = [
                'nodeId' => $node['id'],
                'nodeType' => $element->getGqlTypeName(),
                'siteId' => $node['siteId'],
                'elementType' => $element::class,
            ];
        }

        return $resolved;
    }
}
