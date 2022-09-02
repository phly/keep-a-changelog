<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use function is_readable;

class IsChangelogReadableListener
{
    public function __invoke(EventInterface $event): void
    {
        $changelogFile = $event->config()->changelogFile();

        if (! is_readable($changelogFile)) {
            $event->changelogFileIsUnreadable($changelogFile);
            return;
        }
    }
}
