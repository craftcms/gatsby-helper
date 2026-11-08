<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Listeners;

use CraftCms\Cms\Gql\Events\GqlSchemaComponentsResolving;

use function CraftCms\Cms\t;

/**
 * Adds the `gatsby:read` component that GraphQL schemas need for Gatsby’s sourcing queries.
 *
 * @since 3.0.0
 */
class RegisterSchemaComponent
{
    public function handle(GqlSchemaComponentsResolving $event): void
    {
        $event->queries['Gatsby'] = [
            'gatsby:read' => ['label' => t('Allow discovery of sourcing data for Gatsby.', category: 'gatsby-helper')],
        ];
    }
}
