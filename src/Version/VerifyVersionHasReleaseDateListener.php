<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogParser;
use Phly\KeepAChangelog\Exception\ChangelogMissingDateException;

use function sprintf;

class VerifyVersionHasReleaseDateListener
{
    public function __invoke(TagReleaseEvent $event) : void
    {
        $parser = new ChangelogParser();

        try {
            $parser->findReleaseDateForVersion(
                $event->changelog(),
                $event->version(),
                true
            );
        } catch (ChangelogMissingDateException $e) {
            $event->taggingFailed();
            $event->output()->writeln(sprintf(
                '<error>Version %s does not have a release date associated with it!</error>',
                $event->version()
            ));
            $event->output()->writeln('<error>You may need to run version:ready first</error>');
        }
    }
}
