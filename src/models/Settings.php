<?php

namespace craft\gatsbyhelper\models;

use craft\base\Model;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    /**
     * The address of the preview server, including protocol and port.
     *
     * @var string
     * @deprecated
     */
    public $previewServerUrl = '';

    /**
     * The full URL where
     * @var string
     */
    public $webhookTarget = '';
}
