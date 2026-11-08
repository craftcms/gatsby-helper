<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Listeners;

use CraftCms\Cms\Element\Events\ElementSaved;
use CraftCms\GatsbyHelper\Builds;

/**
 * Triggers a Gatsby build when an element is saved, unless it belongs to a draft or revision.
 *
 * @since 3.0.0
 */
class TriggerBuild
{
    public function __construct(
        private readonly Builds $builds,
    ) {}

    public function handle(ElementSaved $event): void
    {
        $root = $event->element->getRootOwner();

        if ($root->getIsDraft() || $root->getIsRevision()) {
            return;
        }

        $this->builds->trigger();
    }
}
