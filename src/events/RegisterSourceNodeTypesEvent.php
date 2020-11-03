<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsbyhelper\events;

use yii\base\Event;

/**
 * RegisterSourceNodesEvent class.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class RegisterSourceNodeTypesEvent extends Event
{
    /**
     * @var array Source node type list
     */
    public $types = [];
}
