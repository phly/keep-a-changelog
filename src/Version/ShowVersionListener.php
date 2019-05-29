<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogParser;
use Phly\KeepAChangelog\Exception;

use function file_get_contents;
use function sprintf;

class ShowVersionListener
{
    public function __invoke(ShowVersionEvent $event) : void
    {
        $changelogEntry = $event->changelogEntry();
        $event->output()->writeln($changelogEntry->contents);
    }
}
