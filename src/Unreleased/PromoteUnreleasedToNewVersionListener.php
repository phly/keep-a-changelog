<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Common\ChangelogEditSpawnerTrait;

use function explode;
use function implode;
use function sprintf;

class PromoteUnreleasedToNewVersionListener
{
    use ChangelogEditSpawnerTrait;

    public function __invoke(PromoteEvent $event): void
    {
        $entry    = $event->changelogEntry();
        $lines    = explode("\n", $entry->contents);
        $lines[0] = sprintf('## %s - %s', $event->newVersion(), $event->releaseDate());

        $this->getChangelogEditor()->update(
            $event->config()->changelogFile(),
            implode("\n", $lines),
            $entry
        );

        $event->changelogReady();
    }
}
