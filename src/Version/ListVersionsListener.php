<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogParser;

use function sprintf;

class ListVersionsListener
{
    public function __invoke(ListVersionsEvent $event) : void
    {
        $output = $event->output();

        $output->writeln('<info>Found the following versions:</info>');
        foreach ((new ChangelogParser())->findAllVersions($event->config()->changelogFile()) as $version => $date) {
            $output->writeln(sprintf(
                '- %s%s(release date: %s)',
                $version,
                str_repeat("\t", strlen($version) < 8 ? 2 : 1),
                $date
            ));
        }
    }
}
