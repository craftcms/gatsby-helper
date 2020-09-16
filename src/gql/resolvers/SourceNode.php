<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsby\gql\resolvers;

use craft\gql\base\Resolver;
use craft\gatsby\Plugin as Gatsby;
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
        return Gatsby::$plugin->getSourceNodes()->getSourceNodeTypes();
    }

}
