<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

interface ChangelogEntryAwareEventInterface extends
    EventInterface,
    VersionAwareEventInterface
{
    /**
     * Notify the event that the changelog entry associated with the
     * version $version in $changelogFile was not found.
     *
     * This method should stop propagation.
     */
    public function changelogEntryNotFound(string $changelogFile, string $version): void;

    /**
     * Update the event with the discovered changelog entry.
     */
    public function discoveredChangelogEntry(ChangelogEntry $entry): void;

    /**
     * Retrieve the changelog entry, if it exists.
     */
    public function changelogEntry(): ?ChangelogEntry;
}
