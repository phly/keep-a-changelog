<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditSpawnerTrait;

use function explode;
use function implode;
use function preg_match;
use function sprintf;

class SetDateForChangelogReleaseListener
{
    use ChangelogEditSpawnerTrait;

    public function __invoke(ReadyLatestChangelogEvent $event): void
    {
        $entry       = $event->changelogEntry();
        $lines       = explode("\n", $entry->contents);
        $versionLine = $lines[0];

        if (null === ($versionLine = $this->injectDate($versionLine, $event->releaseDate()))) {
            $event->malformedReleaseLine($lines[0]);
            return;
        }

        $lines[0] = $versionLine;

        $this->getChangelogEditor()->update(
            $event->config()->changelogFile(),
            implode("\n", $lines),
            $entry
        );

        $event->changelogReady();
    }

    private function injectDate(string $versionLine, string $date): ?string
    {
        // @phpcs:disable
        $regex = '/^(?P<prefix>## \d+\.\d+\.\d+(?:(alpha|beta|rc|dev|patch|pl|a|b|p)\d+)?)\s+-\s+(?:(?!\d{4}-\d{2}-\d{2}).*)/i';
        // @phpcs:enable

        if (! preg_match($regex, $versionLine, $matches)) {
            return null;
        }

        return sprintf(
            '%s - %s',
            $matches['prefix'],
            $date
        );
    }
}
