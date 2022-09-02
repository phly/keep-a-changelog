<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

class ShowVersionListener
{
    public function __invoke(ShowVersionEvent $event): void
    {
        $changelogEntry = $event->changelogEntry();
        $event->output()->writeln($changelogEntry->contents);
    }
}
