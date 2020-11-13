<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsbyhelper\gql\types;

use craft\gql\base\ObjectType;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeManager;
use GraphQL\Type\Definition\Type;

/**
 * Class ChangedNode
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class ChangedNode extends ObjectType
{
    /**
     * @return string|null
     */
    public static function getName(): string
    {
        return 'UpdatedNode';
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
            'description' => 'Updated data node.',
        ]));

        return $type;
    }

    /**
     * @return array
     */
    public static function getFieldDefinitions(): array
    {
        return TypeManager::prepareFieldDefinitions([
            'nodeType' => [
                'name' => 'nodeType',
                'type' => Type::nonNull(Type::string()),
                'description' => 'Node type.'
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
