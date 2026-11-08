<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper;

use CraftCms\Cms\Database\Table;
use CraftCms\Cms\Element\Contracts\ElementInterface;
use CraftCms\Cms\Field\Elements\ContentBlock;
use CraftCms\Cms\Site\Sites;
use CraftCms\Cms\Support\DateTimeHelper;
use CraftCms\Cms\Support\Query;
use CraftCms\GatsbyHelper\Events\IgnoredTypesResolving;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\DB;

/**
 * Tracks which elements changed, so Gatsby can source just those.
 *
 * @since 1.0.0
 */
#[Singleton]
class Deltas
{
    public const string TABLE = 'gatsby_deletedelements';

    public function __construct(
        private readonly Sites $sites,
    ) {}

    /**
     * Returns the last time content was updated or deleted.
     */
    public function lastContentUpdateTime(): ?string
    {
        $lastUpdated = DB::table(Table::ELEMENTS)->whereNull('dateDeleted')->max('dateUpdated');
        $lastDeleted = DB::table(self::TABLE)->max('dateDeleted');

        if (! $lastDeleted || ! $lastUpdated) {
            return $lastUpdated ?: $lastDeleted ?: null;
        }

        return DateTimeHelper::toDateTime($lastUpdated) < DateTimeHelper::toDateTime($lastDeleted)
            ? $lastDeleted
            : $lastUpdated;
    }

    /**
     * Returns the elements updated, or moved in a structure, since a point in time.
     *
     * @param  int[]  $siteIds
     * @return list<array{id: int, type: class-string<ElementInterface>, siteId: int}>
     */
    public function updatedSince(string $timestamp, array $siteIds): array
    {
        $since = Query::prepareDateForDb($timestamp);

        $movedInStructures = DB::table(Table::STRUCTUREELEMENTS)
            ->where('dateUpdated', '>', $since)
            ->whereNotNull('elementId')
            ->pluck('elementId');

        return DB::table(Table::ELEMENTS, 'e')
            ->join(Table::ELEMENTS_SITES.' as es', 'e.id', '=', 'es.elementId')
            ->whereIn('es.siteId', $siteIds)
            ->whereNull('e.dateDeleted')
            ->whereNotIn('e.type', $this->ignoredTypes())
            ->where(fn ($query) => $query
                ->where('e.dateUpdated', '>', $since)
                ->orWhereIn('e.id', $movedInStructures))
            ->whereNull('e.revisionId')
            ->whereNull('e.draftId')
            ->get(['e.id', 'e.type', 'es.siteId'])
            ->map(fn (object $row) => ['id' => (int) $row->id, 'type' => $row->type, 'siteId' => (int) $row->siteId])
            ->all();
    }

    /**
     * Returns the elements deleted since a point in time.
     *
     * @return list<array{elementId: int, siteId: int, typeName: string}>
     */
    public function deletedSince(string $timestamp): array
    {
        return DB::table(self::TABLE)
            ->where('dateDeleted', '>', Query::prepareDateForDb($timestamp))
            ->get(['elementId', 'siteId', 'typeName'])
            ->map(fn (object $row) => ['elementId' => (int) $row->elementId, 'siteId' => (int) $row->siteId, 'typeName' => $row->typeName])
            ->all();
    }

    /**
     * Records a deleted element in every site, unless its type is ignored.
     */
    public function recordDeletion(ElementInterface $element): bool
    {
        if (in_array($element::class, $this->ignoredTypes(), true)) {
            return false;
        }

        // In case this is being deleted _again_
        DB::table(self::TABLE)->where('elementId', $element->id)->delete();

        $now = Query::prepareDateForDb('now');
        $typeName = $element->getGqlTypeName();

        return DB::table(self::TABLE)->insert(
            $this->sites->getAllSiteIds(true)
                ->map(fn (int $siteId) => [
                    'elementId' => $element->id,
                    'siteId' => $siteId,
                    'typeName' => $typeName,
                    'dateDeleted' => $now,
                ])
                ->all(),
        );
    }

    /**
     * Returns the element types whose changes aren’t reported.
     *
     * @return list<class-string>
     */
    public function ignoredTypes(): array
    {
        event($event = new IgnoredTypesResolving([ContentBlock::class]));

        return $event->types;
    }
}
