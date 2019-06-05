<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Common\ChangelogParser;

class FindChangelogLinksListener
{
    public function __invoke(EditChangelogLinksEvent $event) : void
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
