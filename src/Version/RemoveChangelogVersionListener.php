<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
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
