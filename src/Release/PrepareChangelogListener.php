<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

class PrepareChangelogListener
{
    public function __invoke(ReleaseEvent $event) : void
    {
        $output = $event->output();
        $output->writeln('<info>Preparing changelog for release</info>');

        $parser = $event->dispatcher()->dispatch(new PrepareChangelogEvent(
            $event->input(),
            $output,
            $event->version()
        ));

        if (! $parser->changelogIsReady()) {
            $event->changelogPreparationFailed();
        }

        $event->discoveredChangelog($parser->changelog());
    }
}
