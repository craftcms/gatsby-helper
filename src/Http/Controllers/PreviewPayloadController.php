<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Http\Controllers;

use CraftCms\Cms\Element\Elements;
use CraftCms\Cms\RouteToken\RouteTokens;
use CraftCms\GatsbyHelper\Http\Requests\PreviewPayloadRequest;
use CraftCms\GatsbyHelper\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Returns what Live Preview should send to the Preview Webhook URL for a preview token.
 *
 * The browser sends the request itself, as Gatsby Helper 2.x did, so a Gatsby preview server
 * the browser can reach doesn’t also need to be reachable from Craft.
 *
 * @since 3.0.0
 */
class PreviewPayloadController
{
    public function __invoke(
        PreviewPayloadRequest $request,
        Settings $settings,
        RouteTokens $tokens,
        Elements $elements,
    ): JsonResponse|Response {
        if (! $url = $settings->previewWebhookUrl()) {
            return response()->noContent();
        }

        $token = $request->validated('previewToken');
        $params = ($tokens->getTokenRoute($token) ?: [])[1] ?? null;

        abort_unless(is_array($params) && isset($params['elementType'], $params['canonicalId'], $params['siteId']), 404);

        $element = $elements->getElementById((int) $params['canonicalId'], $params['elementType'], (int) $params['siteId']);

        abort_if($element === null, 404);

        return new JsonResponse([
            'url' => $url,
            'payload' => [
                'operation' => 'update',
                'typeName' => $element->getGqlTypeName(),
                'id' => $element->id,
                'siteId' => $element->siteId,
                'token' => $token,
            ],
        ]);
    }
}
