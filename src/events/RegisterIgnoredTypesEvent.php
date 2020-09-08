<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gatsby\events;

use yii\base\Event;

/**
 * RegisterIgnoredTypesEvent class.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class RegisterIgnoredTypesEvent extends Event
{
    /**
     * @var array List of element type classes ignored for change tracking
     */
    public $types = [];
}
