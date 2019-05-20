<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\ChangelogFormatter;

class FormatChangelogListener
{
    public function __invoke(ReleaseEvent $event) : void
    {
        $formatter = new ChangelogFormatter();
        $event->discoveredChangelog(
            $formatter->format($event->changelog())
        );
    }
}
