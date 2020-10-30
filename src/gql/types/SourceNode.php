<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsby\gql\types;

use craft\gql\base\ObjectType;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeManager;
use GraphQL\Type\Definition\Type;

/**
 * Class SourceNode
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class SourceNode extends ObjectType
{
    /**
     * @return string|null
     */
    public static function getName(): string
    {
        return 'SourceNode';
    }

    /**
     * @return Type
     */
    public static function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new self([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'Source node definition.',
        ]));

        return $type;
    }

    /**
     * @return array
     */
    public static function getFieldDefinitions(): array
    {
        return TypeManager::prepareFieldDefinitions([
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
                'description' => 'Regular expression to apply to a specific node type name to extract the node type. The first matched group will be used as the value for filter argument. ',
            ],
            'targetInterface' => [
                'name' => 'targetInterface',
                'type' => Type::string(),
                'description' => 'Which target interface should be used to infer specific type implementations.'
            ]
        ], self::getName());
    }
}
