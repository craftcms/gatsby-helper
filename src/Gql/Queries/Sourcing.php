<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Gql\Queries;

use CraftCms\Cms\Cms;
use CraftCms\Cms\Gql\GqlHelper;
use CraftCms\Cms\Gql\Queries\Query;
use CraftCms\Cms\Gql\Types\DateTime;
use CraftCms\Cms\Shared\Models\Info;
use CraftCms\Cms\Site\Sites;
use CraftCms\GatsbyHelper\Deltas;
use CraftCms\GatsbyHelper\Gql\Resolvers\DeletedNode;
use CraftCms\GatsbyHelper\Gql\Resolvers\SourceNode as SourceNodeResolver;
use CraftCms\GatsbyHelper\Gql\Resolvers\UpdatedNode;
use CraftCms\GatsbyHelper\Gql\Types\ChangedNode;
use CraftCms\GatsbyHelper\Gql\Types\SourceNode;
use CraftCms\GatsbyHelper\Plugin;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

/**
 * The queries gatsby-source-craft uses to source content. Schemas need the `gatsby:read` component.
 *
 * @phpstan-import-type UnnamedFieldDefinitionConfig from FieldDefinition
 *
 * @since 1.0.0
 */
class Sourcing extends Query
{
    /** @return array<string, UnnamedFieldDefinitionConfig> */
    public static function getQueries(bool $checkToken = true): array
    {
        if ($checkToken && ! GqlHelper::canSchema('gatsby')) {
            return [];
        }

        return [
            'sourceNodeInformation' => [
                'type' => Type::listOf(SourceNode::getType()),
                'resolve' => SourceNodeResolver::class.'::resolve',
                'description' => 'Return sourcing data for Gatsby source queries.',
            ],
            'configVersion' => [
                'type' => Type::string(),
                'resolve' => fn () => Info::fetch()->getAttribute('configVersion'),
                'description' => 'Return the current config version.',
            ],
            'lastUpdateTime' => [
                'type' => DateTime::getType(),
                'resolve' => fn () => app(Deltas::class)->lastContentUpdateTime(),
                'description' => 'Return the last time content was updated on this site.',
            ],
            'primarySiteId' => [
                'type' => Type::string(),
                'resolve' => fn () => app(Sites::class)->getPrimarySite()->handle,
                'description' => 'Return the primary site id.',
            ],
            'nodesUpdatedSince' => [
                'type' => Type::listOf(ChangedNode::getType()),
                'args' => [
                    'since' => [
                        'name' => 'since',
                        'type' => Type::nonNull(Type::string()),
                    ],
                    'site' => [
                        'name' => 'site',
                        'type' => Type::listOf(Type::string()),
                        'description' => 'Determines which site(s) the elements should be queried in. Defaults to the primary site.',
                    ],
                ],
                'resolve' => UpdatedNode::class.'::resolve',
                'description' => 'Return the list of nodes updated since a point in time.',
            ],
            'nodesDeletedSince' => [
                'type' => Type::listOf(ChangedNode::getType()),
                'args' => [
                    'since' => [
                        'name' => 'since',
                        'type' => Type::nonNull(Type::string()),
                    ],
                ],
                'resolve' => DeletedNode::class.'::resolve',
                'description' => 'Return the list of nodes deleted since a point in time.',
            ],
            'gatsbyHelperVersion' => [
                'type' => Type::string(),
                'resolve' => fn () => app(Plugin::class)->version,
                'description' => 'Return the version of the installed Gatsby Helper plugin.',
            ],
            'gqlTypePrefix' => [
                'type' => Type::string(),
                'resolve' => fn () => Cms::config()->gqlTypePrefix,
                'description' => 'Return the value of the `gqlTypePrefix` config setting.',
            ],
            'craftVersion' => [
                'type' => Type::string(),
                'resolve' => fn () => Cms::version(),
                'description' => 'Return the installed Craft version.',
            ],
        ];
    }
}
