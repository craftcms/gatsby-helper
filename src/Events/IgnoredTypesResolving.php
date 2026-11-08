<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Events;

/**
 * Lets plugins and modules list element types whose changes shouldn’t be reported to Gatsby.
 *
 * ```php
 * use CraftCms\GatsbyHelper\Events\IgnoredTypesResolving;
 * use Illuminate\Support\Facades\Event;
 *
 * Event::listen(function (IgnoredTypesResolving $event) {
 *     $event->types[] = MyElement::class;
 * });
 * ```
 *
 * @since 3.0.0
 */
class IgnoredTypesResolving
{
    public function __construct(
        /** @var list<class-string> Element types that are ignored for change tracking */
        public array $types = [],
    ) {}
}
