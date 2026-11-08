<?php

declare(strict_types=1);

use CraftCms\Cms\Auth\SessionAuth;
use CraftCms\Cms\Edition;
use CraftCms\Cms\Entry\Elements\Entry;
use CraftCms\Cms\User\Elements\User;
use CraftCms\Cms\User\Models\User as UserModel;
use CraftCms\GatsbyHelper\Settings;
use CraftCms\GatsbyHelper\Tests\TestCase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Edition::set(Edition::Pro);
    Http::fake();
    $this->entry = blogEntry();
});

/**
 * Creates a preview token the way Live Preview does.
 */
function previewToken(Entry $entry): string
{
    SessionAuth::authorize("previewElement:$entry->id");

    return postJson('/admin/actions/preview/create-token', [
        'elementType' => $entry::class,
        'canonicalId' => $entry->id,
        'siteId' => $entry->siteId,
    ])->assertOk()->json('token');
}

it('returns what Live Preview sends to Gatsby', function () {
    actingAs(User::find()->admin()->one());
    $token = previewToken($this->entry);

    getJson('/admin/actions/gatsby-helper/preview-payload?previewToken='.$token)
        ->assertOk()
        ->assertExactJson([
            'url' => TestCase::PREVIEW_URL,
            'payload' => [
                'operation' => 'update',
                'typeName' => $this->entry->getGqlTypeName(),
                'id' => $this->entry->id,
                'siteId' => $this->entry->siteId,
                'token' => $token,
            ],
        ]);
});

it('requires a control panel user', function () {
    getJson('/actions/gatsby-helper/preview-payload?previewToken=abc')->assertUnauthorized();

    actingAs(UserModel::factory()->createElement());
    getJson('/actions/gatsby-helper/preview-payload?previewToken=abc')->assertForbidden();
});

it('rejects unknown tokens', function () {
    actingAs(User::find()->admin()->one());

    getJson('/admin/actions/gatsby-helper/preview-payload?previewToken=unknown')->assertNotFound();
});

it('returns nothing without a Preview Webhook URL', function () {
    actingAs(User::find()->admin()->one());
    $token = previewToken($this->entry);
    app(Settings::class)->previewWebhookUrl = '';

    getJson('/admin/actions/gatsby-helper/preview-payload?previewToken='.$token)->assertNoContent();
});
