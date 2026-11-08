<?php

declare(strict_types=1);

use CraftCms\Cms\Cms;
use CraftCms\Cms\Entry\Elements\Entry;
use CraftCms\Cms\Support\Facades\Elements;
use CraftCms\GatsbyHelper\Events\IgnoredTypesResolving;
use CraftCms\GatsbyHelper\Events\SourceNodeTypesResolving;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake();
    Cms::config()->enableGraphqlCaching = false;
});

it('only exposes the sourcing queries to schemas with the gatsby:read component', function () {
    blogEntry();

    expect(gql('{ configVersion }', components: [])['data'] ?? [])->not->toHaveKey('configVersion')
        ->and(gql('{ configVersion }')['data']['configVersion'])->toHaveLength(12);
});

it('describes the source node types the schema can query', function () {
    blogEntry();

    expect(gql('{ sourceNodeInformation { node list filterArgument filterTypeExpression targetInterface } }')['data']['sourceNodeInformation'])
        ->toBe([[
            'node' => 'entry',
            'list' => 'entries',
            'filterArgument' => 'type',
            'filterTypeExpression' => '(?:.+)_(.+)_Entry+$',
            'targetInterface' => 'EntryInterface',
        ]]);
});

it('lets listeners add source node types', function () {
    blogEntry();
    Event::listen(fn (SourceNodeTypesResolving $event) => $event->types['BookInterface'] = [
        'node' => 'book',
        'list' => 'books',
        'filterArgument' => 'type',
        'filterTypeExpression' => '(.+)_Book',
        'targetInterface' => 'BookInterface',
    ]);

    expect(array_column(gql('{ sourceNodeInformation { targetInterface } }')['data']['sourceNodeInformation'], 'targetInterface'))
        ->toBe(['EntryInterface', 'BookInterface']);
});

it('lists nodes updated and deleted since a point in time', function () {
    $untouched = blogEntry(['title' => 'Untouched']);
    $updated = blogEntry();
    $deleted = blogEntry(['title' => 'Gone']);
    Elements::deleteElement($deleted);

    $this->travelTo(Date::parse('2040-01-01 12:00:00', 'UTC'));
    Elements::saveElement($updated);
    Elements::deleteElement($later = blogEntry(['title' => 'Gone later']));

    $data = gql('{
        nodesUpdatedSince(since: "2039-12-31") { nodeId nodeType siteId elementType }
        nodesDeletedSince(since: "2039-12-31") { nodeId nodeType siteId }
    }')['data'];

    expect($data['nodesUpdatedSince'])->toBe([[
        'nodeId' => (string) $updated->id,
        'nodeType' => $updated->getGqlTypeName(),
        'siteId' => (string) $updated->siteId,
        'elementType' => Entry::class,
    ]])->and($data['nodesDeletedSince'])->toBe([[
        'nodeId' => (string) $later->id,
        'nodeType' => $later->getGqlTypeName(),
        'siteId' => (string) $later->siteId,
    ]])
        ->and(array_column($data['nodesUpdatedSince'], 'nodeId'))->not->toContain((string) $untouched->id);

    expect(gql('{ nodesDeletedSince(since: "2000-01-01") { nodeId } }')['data']['nodesDeletedSince'])
        ->toContain(['nodeId' => (string) $deleted->id]);
});

it('only lists nodes in the requested sites', function () {
    $this->travelTo(Date::parse('2040-01-01 12:00:00', 'UTC'));
    Elements::saveElement(blogEntry());

    expect(gql('{ nodesUpdatedSince(since: "2039-12-31", site: ["missing"]) { nodeId } }')['data']['nodesUpdatedSince'])->toBe([])
        ->and(gql('{ nodesUpdatedSince(since: "2039-12-31", site: ["default"]) { nodeId } }')['data']['nodesUpdatedSince'])->toHaveCount(1);
});

it('lets listeners ignore element types', function () {
    $this->travelTo(Date::parse('2040-01-01 12:00:00', 'UTC'));
    Elements::saveElement(blogEntry());
    Event::listen(fn (IgnoredTypesResolving $event) => $event->types[] = Entry::class);
    Elements::deleteElement(blogEntry(['title' => 'Gone']));

    expect(gql('{ nodesUpdatedSince(since: "2039-12-31") { nodeId } nodesDeletedSince(since: "2039-12-31") { nodeId } }')['data'])
        ->toBe(['nodesUpdatedSince' => [], 'nodesDeletedSince' => []]);
});

it('reports the site’s state to the source plugin', function () {
    $entry = blogEntry();

    expect(gql('{ primarySiteId gqlTypePrefix craftVersion gatsbyHelperVersion }')['data'])
        ->toMatchArray([
            'primarySiteId' => 'default',
            'gqlTypePrefix' => '',
            'craftVersion' => Cms::version(),
        ]);

    // A deletion counts as a content update
    $this->travelTo(Date::parse('2040-01-01 12:00:00', 'UTC'));
    Elements::deleteElement($entry);

    expect(gql('{ lastUpdateTime @formatDateTime(format: "Y-m-d H:i:s", timezone: "UTC") }')['data']['lastUpdateTime'])
        ->toBe('2040-01-01 12:00:00');
});
