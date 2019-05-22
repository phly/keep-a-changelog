<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Phly\KeepAChangelog\ChangelogParser;
use Phly\KeepAChangelog\Exception;

class ParseChangelogListener
{
    public function __invoke(ChangelogAwareEventInterface $event) : void
    {
        $changelogFile = $event->config()->changelogFile();
        $parser = new ChangelogParser();
        try {
            $changelog = $parser->findChangelogForVersion(
                file_get_contents($changelogFile),
                $event->version()
            );
        } catch (Exception\ExceptionInterface $e) {
            $event->errorParsingChangelog($changelogFile, $e);
            return;
        }

        $event->updateChangelog($changelog);
    }
}
