<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Gql\Types;

use CraftCms\Cms\Gql\GqlEntityRegistry;
use CraftCms\Cms\Gql\Types\ObjectType;
use CraftCms\Cms\Support\Facades\Gql;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

/**
 * How Gatsby queries one type of source node.
 *
 * @phpstan-import-type FieldDefinitionConfig from FieldDefinition
 *
 * @since 1.0.0
 */
class SourceNode extends ObjectType
{
    public static function getName(): string
    {
        return 'SourceNode';
    }

    public static function getType(): Type
    {
        return GqlEntityRegistry::getOrCreate(self::getName(), fn () => new self([
            'name' => self::getName(),
            'fields' => self::class.'::getFieldDefinitions',
            'description' => 'Source node definition.',
        ]));
    }

    /** @return array<string, FieldDefinitionConfig> */
    public static function getFieldDefinitions(): array
    {
        return Gql::prepareFieldDefinitions([
            'list' => [
                'name' => 'list',
                'type' => Type::nonNull(Type::string()),
                'description' => 'The GraphQL query to use for fetching a node list of this source node type.',
            ],
            'node' => [
                'name' => 'node',
                'type' => Type::nonNull(Type::string()),
                'description' => 'The GraphQL query to use for fetching a specific node of this source node type.',
            ],
            'filterArgument' => [
                'name' => 'filterArgument',
                'type' => Type::string(),
                'description' => 'The name of the argument to use when querying for a list of nodes to narrow down a specific type of nodes.',
            ],
            'filterTypeExpression' => [
                'name' => 'filterTypeExpression',
                'type' => Type::string(),
                'description' => 'Regular expression to apply to a specific node type name to extract the node type. The first matched group will be used as the value for filter argument.',
            ],
            'targetInterface' => [
                'name' => 'targetInterface',
                'type' => Type::string(),
                'description' => 'Which target interface should be used to infer specific type implementations.',
            ],
        ], self::getName());
    }
}
