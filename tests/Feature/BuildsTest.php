<?php

declare(strict_types=1);

use CraftCms\Cms\Element\Drafts;
use CraftCms\Cms\Support\Facades\Elements;
use CraftCms\GatsbyHelper\Deltas;
use CraftCms\GatsbyHelper\Settings;
use CraftCms\GatsbyHelper\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('sends one build request once the request is done, however many elements were saved', function () {
    $entries = [blogEntry(), blogEntry(['title' => 'Another story'])];
    Http::fake();

    foreach ($entries as $entry) {
        Elements::saveElement($entry);
    }

    Http::assertNothingSent();

    finishRequest();

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request->url() === TestCase::BUILD_URL
        && $request->method() === 'POST'
        && $request->header('x-preview-update-source') === ['Craft CMS']
        && $request->body() === '');
});

it('doesn’t build for drafts', function () {
    $entry = blogEntry();
    Http::fake();

    $draft = app(Drafts::class)->createDraft($entry, 1);
    $draft->title = 'Draft change';
    Elements::saveElement($draft);
    Elements::deleteElement($draft, true);
    finishRequest();

    Http::assertNothingSent();
});

it('doesn’t build without a Build Webhook URL', function () {
    Http::fake();
    app(Settings::class)->buildWebhookUrl = '';

    Elements::saveElement(blogEntry());
    finishRequest();

    Http::assertNothingSent();
});

it('resolves environment variables in the Build Webhook URL', function () {
    Http::fake();
    $_SERVER['GATSBY_TEST_BUILD_URL'] = 'https://env.gatsby.test/build';
    app(Settings::class)->buildWebhookUrl = '$GATSBY_TEST_BUILD_URL';

    Elements::saveElement(blogEntry());
    finishRequest();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://env.gatsby.test/build');
});

it('logs a failing build request instead of throwing', function () {
    Http::fake([TestCase::BUILD_URL => Http::response('nope', 500)]);
    Log::spy();

    Elements::saveElement(blogEntry());
    finishRequest();

    Log::shouldHaveReceived('error')->withArgs(fn (string $message) => str_starts_with($message, 'Unable to trigger a Gatsby build'));
});

it('records deleted elements in every site and builds', function () {
    $entry = blogEntry();
    Http::fake();

    Elements::deleteElement($entry);
    finishRequest();

    expect(DB::table(Deltas::TABLE)->get(['elementId', 'typeName'])->map(fn ($row) => (array) $row)->all())
        ->toBe([['elementId' => $entry->id, 'typeName' => $entry->getGqlTypeName()]]);
    Http::assertSentCount(1);
});
