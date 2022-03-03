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
    public string $previewServerUrl = '';

    /**
     * The full URL where the plugin should let Gatsby know to trigger preview
     * @var string
     */
    public string $previewWebhookUrl = '';

    /**
     * The full URL where the plugin should let Gatsby know to trigger a site build
     * @var string
     */
    public string $buildWebhookUrl = '';
}
