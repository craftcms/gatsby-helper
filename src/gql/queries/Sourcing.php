<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsbyhelper\gql\queries;

use Craft;
use craft\gatsbyhelper\gql\resolvers\DeletedNode as DeletedNodeResolver;
use craft\gatsbyhelper\gql\resolvers\SourceNode as SourceNodeResolver;
use craft\gatsbyhelper\gql\resolvers\UpdatedNode as UpdatedNodeResolver;
use craft\gatsbyhelper\gql\types\ChangedNode;
use craft\gatsbyhelper\gql\types\GatsbyMeta;
use craft\gatsbyhelper\gql\types\SourceNode;
use craft\gatsbyhelper\helpers\Gql as GqlHelper;
use craft\gatsbyhelper\Plugin;
use craft\gql\base\Query;
use craft\gql\types\DateTime;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

/**
 * Class Sourcing
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class Sourcing extends Query
{
    /**
     * @inheritdoc
     */
    public static function getQueries($checkToken = true): array
    {
        if ($checkToken && !GqlHelper::canQueryGatsbyData()) {
            return [];
        }

        return [
            'sourceNodeInformation' => [
                'type' => Type::listOf(SourceNode::getType()),
                'resolve' => SourceNodeResolver::class . '::resolve',
                'description' => 'Return sourcing data for Gatsby source queries.'
            ],
            'configVersion' => [
                'type' => Type::string(),
                'resolve' => function() {
                    return Craft::$app->getInfo()->configVersion;
                },
                'description' => 'Return the current config version.'
            ],
            'lastUpdateTime' => [
                'type' => DateTime::getType(),
                'resolve' => function() {
                    return Plugin::getInstance()->getDeltas()->getLastContentUpdateTime();
                },
                'description' => 'Return the last time content was updated on this site.'
            ],
            'primarySiteId' => [
                'type' => Type::string(),
                'resolve' => function () { return Craft::$app->getSites()->getPrimarySite()->handle; },
                'description' => 'Return the primary site id.'
            ],
            'nodesUpdatedSince' => [
                'type' => Type::listOf(ChangedNode::getType()),
                'args' => [
                    'since' => [
                        'name' => 'since',
                        'type' => Type::nonNull(Type::string())
                    ],
                    'site' => [
                        'name' => 'site',
                        'type' => Type::listOf(Type::string()),
                        'description' => 'Determines which site(s) the elements should be queried in. Defaults to the current (requested) site.',
                    ],
                ],
                'resolve' => UpdatedNodeResolver::class . '::resolve',
                'description' => 'Return the list of nodes updated since a point in time.'
            ],
            'nodesDeletedSince' => [
                'type' => Type::listOf(ChangedNode::getType()),
                'args' => [
                    'since' => [
                        'name' => 'since',
                        'type' => Type::nonNull(Type::string())
                    ],
                ],
                'resolve' => DeletedNodeResolver::class . '::resolve',
                'description' => 'Return the list of nodes deleted since a point in time.'
            ],
            'gatsbyHelperVersion' => [
                'type' => Type::string(),
                'resolve' => function () {
                    return Plugin::getInstance()->version;
                },
                'description' => 'Return the verison of the currently installed Helper plugin version.'
            ],
            'gqlTypePrefix' => [
                'name' => 'gqlTypePrefix',
                'type' => Type::string(),
                'resolve' => function () {
                    return Craft::$app->getConfig()->getGeneral()->gqlTypePrefix;
                },
                'description' => 'Return the value of the `gqlTypePrefix` config setting.'
            ],
            'craftVersion' => [
                'name' => 'craftVersion',
                'type' => Type::string(),
                'description' => 'Return the value of the `gqlTypePrefix` config setting.',
                'resolve' => function () {
                     return Craft::$app->version;
                },
            ]
        ];
    }
}
