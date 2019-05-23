<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\ChangelogParser;

use function sprintf;

class ListVersionsListener
{
    public function __invoke(ListVersionsEvent $event) : void
    {
        $output = $event->output();

        $output->writeln('<info>Found the following versions:</info>');
        foreach ((new ChangelogParser())->findAllVersions($event->changelogFile()) as $version => $date) {
            $output->writeln(sprintf('- %s (release date: %s)', $version, $date));
        }
    }
}
