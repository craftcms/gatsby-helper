<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsby\helpers;

use craft\helpers\Gql as GqlHelper;

/**
 * Class Gql
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class Gql extends GqlHelper
{
    /**
     * Return true if active schema can query gatsby data.
     *
     * @return bool
     */
    public static function canQueryGatsbyData(): bool
    {
        $allowedEntities = self::extractAllowedEntitiesFromSchema();
        return isset($allowedEntities['gatsby']);
    }
}
