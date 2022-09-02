<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditSpawnerTrait;

class RemoveChangelogVersionListener
{
    use ChangelogEditSpawnerTrait;

    public function __invoke(RemoveChangelogVersionEvent $event): void
    {
        $changelog = $event->config()->changelogFile();
        $entry     = $event->changelogEntry();

        $this->getChangelogEditor()->update($changelog, '', $entry);

        $event->versionRemoved();
    }
}
