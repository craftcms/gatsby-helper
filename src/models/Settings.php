<?php
namespace craft\gatsby\models;

use \PHP_Typography\Settings\Dash_Style;
use \PHP_Typography\Settings\Quote_Style;

use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * The address of the preview server, including protocol and port.
     *
     * @var boolean
     */
    public $previewServerUrl = '';
}
