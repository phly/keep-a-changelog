<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

class IsEntryArgumentEmptyListener
{
    public function __invoke(AddChangelogEntryEvent $event): void
    {
        if (! $event->entry()) {
            $event->entryIsEmpty();
        }
    }
}
