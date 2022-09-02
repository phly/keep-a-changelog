<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Common\ChangelogParser;

class FindChangelogLinksListener
{
    public function __invoke(EditChangelogLinksEvent $event): void
    {
        $changelog = $event->config()->changelogFile();
        $links     = (new ChangelogParser())->findLinks($changelog);

        if (null === $links->index) {
            $event->noLinksDiscovered();
            return;
        }

        $event->discoveredLinks($links);
    }
}
