<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsbyhelper\gql\resolvers;

use Craft;
use craft\gatsbyhelper\Plugin;
use craft\gql\base\Resolver;
use craft\gql\interfaces\Element as ElementInterface;
use craft\helpers\ElementHelper;
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
        $updatedNodes = Plugin::getInstance()->getDeltas()->getUpdatedNodesSinceTimeStamp($arguments['since']);
        $resolved = [];
        $allowedInterfaces = array_keys(Plugin::getInstance()->getSourceNodes()->getSourceNodeTypes());
        $schema = Craft::$app->getGql()->getSchemaDef();

        foreach ($updatedNodes as $elementId => $elementType) {
            $element = Craft::$app->getElements()->getElementById($elementId, $elementType);
            $gqlType = $schema->getType($element->getGqlTypeName());
            $registeredInterfaces = $gqlType->getInterfaces();

            foreach ($registeredInterfaces as $registeredInterface) {
                $interfaceName = $registeredInterface->name;
                // Make sure Gatsby can handle updates to these elements.
                if ($interfaceName !== ElementInterface::getName() && !in_array($interfaceName, $allowedInterfaces, true)) {
                    continue 2;
                }
            }

            if ($element && $gqlType) {
                foreach (ElementHelper::supportedSitesForElement($element) as $site) {
                    $resolved[] = [
                        'nodeId' => $elementId,
                        'nodeType' => $element->getGqlTypeName(),
                        'siteId' => $site['siteId']
                    ];
                }
            }
        }

        return $resolved;
    }
}
