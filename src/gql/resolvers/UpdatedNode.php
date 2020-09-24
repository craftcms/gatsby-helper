<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsby\gql\resolvers;

use Craft;
use craft\gql\base\Resolver;
use craft\gatsby\Plugin as Gatsby;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class UpdatedNode
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class UpdatedNode extends Resolver
{
    public static function resolve($source, array $arguments, $context, ResolveInfo $resolveInfo)
    {
        $updatedNodes = Gatsby::$plugin->getDeltas()->getUpdatedNodesSinceTimeStamp($arguments['since']);
        $resolved = [];

        foreach ($updatedNodes as $elementId => $elementType) {
            $element = Craft::$app->getElements()->getElementById($elementId, $elementType);

            if ($element) {
                $resolved[] = [
                    'nodeId' => $elementId,
                    'nodeType' => $element->getGqlTypeName()
                ];
            }
        }

        return $resolved;
    }
}
