<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Ready;

use Phly\KeepAChangelog\ChangelogEditor;

use function explode;
use function implode;
use function preg_match;
use function sprintf;

class SetDateInChangelogEntryListener
{
    public function __invoke(ReadyLatestChangelogEvent $event) : void
    {
        $entry       = $event->changelogEntry();
        $lines       = explode("\n", $entry->contents);
        $versionLine = $lines[0];

        if (null === ($versionLine = $this->injectDate($versionLine, $event->releaseDate()))) {
            $event->malformedReleaseLine();
            return;
        }

        $lines[0] = $versionLine;

        (new ChangelogEditor())->update(
            $event->changelogFile(),
            implode("\n", $lines),
            $entry->index,
            $entry->length,
        );

        $event->changelogReady();
    }

    private function injectDate(string $versionLine, string $date) : ?string
    {
        // @phpcs:disable
        $regex = '/^(?P<prefix>## \d+\.\d+\.\d+(?:(alpha|beta|rc|dev|patch|pl|a|b|p)\d+)?)\s+-\s+(?:(?!\d{4}-\d{2}-\d{2}).*)/i';
        // @phpcs:enable

        if (! preg_match($regex, $versionLine, $matches)) {
            return null;
        }

        return sprintf(
            "%s - %s\n",
            $matches['prefix'],
            $date
        );
    }
}
