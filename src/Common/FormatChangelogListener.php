<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

class FormatChangelogListener
{
    public function __invoke(ChangelogAwareEventInterface $event): void
    {
        $formatter = new ChangelogFormatter();
        $event->updateChangelog(
            $formatter->format($event->changelog())
        );
    }
}
