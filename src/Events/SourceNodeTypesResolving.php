<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Events;

/**
 * Lets plugins and modules add element types that Gatsby should source, indexed by their GraphQL interface name.
 *
 * ```php
 * use CraftCms\GatsbyHelper\Events\SourceNodeTypesResolving;
 * use Illuminate\Support\Facades\Event;
 *
 * Event::listen(function (SourceNodeTypesResolving $event) {
 *     $event->types[BookInterface::getName()] = [
 *         'node' => 'book',
 *         'list' => 'books',
 *         'filterArgument' => 'type',
 *         'filterTypeExpression' => '(.+)_Book',
 *         'targetInterface' => BookInterface::getName(),
 *     ];
 * });
 * ```
 *
 * @since 3.0.0
 */
class SourceNodeTypesResolving
{
    public function __construct(
        /** @var array<string, array{node: string, list: string, filterArgument: string, filterTypeExpression: string, targetInterface: string}> Source node types, indexed by interface name */
        public array $types = [],
    ) {}
}
