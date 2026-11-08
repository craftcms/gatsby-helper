<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Listeners;

use CraftCms\Cms\Element\Events\ElementDeleted;
use CraftCms\GatsbyHelper\Builds;
use CraftCms\GatsbyHelper\Deltas;

/**
 * Records a deleted element for `nodesDeletedSince`, and triggers a Gatsby build unless it belonged to a draft or revision.
 *
 * @since 3.0.0
 */
class RecordDeletion
{
    public function __construct(
        private readonly Deltas $deltas,
        private readonly Builds $builds,
    ) {}

    public function handle(ElementDeleted $event): void
    {
        $this->deltas->recordDeletion($event->element);

        $root = $event->element->getRootOwner();

        if ($root->getIsDraft() || $root->getIsRevision()) {
            return;
        }

        $this->builds->trigger();
    }
}
