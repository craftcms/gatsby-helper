<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Gql\Types;

use CraftCms\Cms\Gql\GqlEntityRegistry;
use CraftCms\Cms\Gql\Types\ObjectType;
use CraftCms\Cms\Support\Facades\Gql;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

/**
 * A node that was updated or deleted. Its GraphQL name is `UpdatedNode`, as Gatsby expects.
 *
 * @phpstan-import-type FieldDefinitionConfig from FieldDefinition
 *
 * @since 1.0.0
 */
class ChangedNode extends ObjectType
{
    public static function getName(): string
    {
        return 'UpdatedNode';
    }

    public static function getType(): Type
    {
        return GqlEntityRegistry::getOrCreate(self::getName(), fn () => new self([
            'name' => self::getName(),
            'fields' => self::class.'::getFieldDefinitions',
            'description' => 'Updated data node.',
        ]));
    }

    /** @return array<string, FieldDefinitionConfig> */
    public static function getFieldDefinitions(): array
    {
        return Gql::prepareFieldDefinitions([
            'nodeType' => [
                'name' => 'nodeType',
                'type' => Type::nonNull(Type::string()),
                'description' => 'Node type.',
            ],
            'elementType' => [
                'name' => 'elementType',
                'type' => Type::string(),
                'description' => 'Element type.',
            ],
            'nodeId' => [
                'name' => 'nodeId',
                'type' => Type::nonNull(Type::id()),
                'description' => 'Node id.',
            ],
            'siteId' => [
                'name' => 'siteId',
                'type' => Type::id(),
                'description' => 'Site id.',
            ],
        ], self::getName());
    }
}
