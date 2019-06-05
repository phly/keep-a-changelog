<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function file;
use function preg_match;
use function preg_quote;
use function sprintf;

/**
 * Retrieves changelog entry from the file.
 *
 * If the event has no version set, it fetches the first entry;
 * otherwise, it attempts to fetch the entry associated with the
 * given version.
 *
 * If no changelog entry is found, it notifies the event. Otherwise,
 * it injects a ChangelogEntry instance in the event for later usage.
 */
class DiscoverChangelogEntryListener
{
    public function __invoke(ChangelogEntryAwareEventInterface $event) : void
    {
        $filename      = $event->config()->changelogFile();
        $version       = $event->version();
        $contents      = file($filename) ?: [];
        $entry         = new ChangelogEntry();
        $boundaryRegex = '/^(?:## (?:\d+\.\d+\.\d+|\[\d+\.\d+\.\d+\])|\[.*?\]:\s*\S+)/';
        $regex         = $version
            ? sprintf(
                '/^## (?:%1$s|\[%1$s\])/',
                preg_quote($version)
            )
            : $boundaryRegex;

        foreach ($contents as $index => $line) {
            if ($entry->index && preg_match($boundaryRegex, $line)) {
                break;
            }

            if (preg_match($regex, $line)) {
                $entry->contents = $line;
                $entry->index    = $index;
                $entry->length   = 1;
                continue;
            }

            if (! $entry->index) {
                continue;
            }

            $entry->contents .= $line;
            $entry->length   += 1;
        }

        if ($entry->index === null) {
            $event->changelogEntryNotFound($filename, $version);
            return;
        }

        $event->discoveredChangelogEntry($entry);
    }
}
