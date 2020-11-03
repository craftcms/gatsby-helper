<?php

namespace craft\gatsbyhelper\models;

use craft\base\Model;
use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\Settings\Quote_Style;

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
