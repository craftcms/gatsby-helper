<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsbyhelper\gql\resolvers;

use craft\gatsbyhelper\Plugin;
use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class SourceNode
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class SourceNode extends Resolver
{
    public static function resolve($source, array $arguments, $context, ResolveInfo $resolveInfo)
    {
        return Plugin::getInstance()->getSourceNodes()->getSourceNodeTypes();
    }

}
