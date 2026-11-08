<?php

declare(strict_types=1);

use CraftCms\Cms\Entry\Elements\Entry;
use CraftCms\Cms\Entry\Models\Entry as EntryModel;
use CraftCms\Cms\Gql\Data\GqlSchema;
use CraftCms\Cms\Gql\Gql;
use CraftCms\Cms\Section\Models\Section;
use CraftCms\Cms\Site\Sites;
use CraftCms\GatsbyHelper\Tests\TestCase;
use Illuminate\Support\Defer\DeferredCallbackCollection;

uses(TestCase::class)->in('Feature');

/**
 * Creates a live entry in the `blog` section.
 *
 * @param  array<string, mixed>  $attributes
 */
function blogEntry(array $attributes = []): Entry
{
    $section = Section::query()->where('handle', 'blog')->first()
        ?? Section::factory()->create(['handle' => 'blog', 'name' => 'Blog']);

    return EntryModel::factory()->createElement([
        'sectionId' => $section->id,
        'title' => 'Gatsby story',
        ...$attributes,
    ]);
}

/**
 * Runs a GraphQL query against a schema that can read the `blog` section, plus the given components.
 *
 * @param  string[]  $components
 * @return array<string, mixed>
 */
function gql(string $query, array $components = ['gatsby:read']): array
{
    $section = Section::query()->where('handle', 'blog')->firstOrFail();
    $gql = app(Gql::class);
    $gql->flushCaches();

    $schema = new GqlSchema(['name' => 'Test', 'scope' => [
        "sections.$section->uid:read",
        ...$section->entryTypes->map(fn ($entryType) => "entrytypes.$entryType->uid:read")->all(),
        'sites.'.app(Sites::class)->getPrimarySite()->uid.':read',
        ...$components,
    ]]);
    $gql->setActiveSchema($schema);

    return $gql->executeQuery($schema, $query);
}

/**
 * Runs the callbacks Laravel defers until the request, command or job is done.
 */
function finishRequest(): void
{
    app(DeferredCallbackCollection::class)->invoke();
}
