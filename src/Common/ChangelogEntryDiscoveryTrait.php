<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function sprintf;

/**
 * Provides an implementation of the methods defined in ChangelogEntryAwareEventInterface.
 *
 * Assumes that the class as defined the `$output`, `$version`,
 * `$changelogFile` properties.
 */
trait ChangelogEntryDiscoveryTrait
{
    /** @var null|ChangelogEntry */
    private $changelogEntry;

    public function changelogEntryNotFound(string $changelogFile, string $version) : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Could not locate version %s in changelog file %s;'
            . ' please verify the version and/or changelog file.</error>',
            $this->version ?: '"latest"',
            $changelogFile
        ));
    }

    public function discoveredChangelogEntry(ChangelogEntry $entry) : void
    {
        $this->changelogEntry = $entry;
    }

    public function changelogEntry() : ?ChangelogEntry
    {
        return $this->changelogEntry;
    }
}
