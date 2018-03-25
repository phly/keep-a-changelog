<?php
/**
 * @see       https://github.com/phly/keep-a-changelog-tagger for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog-tagger/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpMinorCommand extends Command
{
    private const HELP = <<< 'EOH'
Add a new minor release entry to the changelog, based on the latest release.

Parses the CHANGELOG.md file to determine the latest release, and creates
a new entry representing the next minor release.

EOH;

    protected function configure() : void
    {
        $this->setDescription('Create a new changelog entry for the next minor release.');
        $this->setHelp(self::HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $cwd = realpath(getcwd());

        $changelogFile = sprintf('%s/CHANGELOG.md', $cwd);
        if (! is_readable($changelogFile)) {
            throw new Exception\ChangelogFileNotFoundException();
        }

        $bumper = new ChangelogBump($changelogFile);
        $latest = $bumper->findLatestVersion();
        $version = $bumper->bumpMinorVersion($latest);
        $bumper->updateChangelog($version);

        $output->writeln(sprintf(
            '<info>Bumped changelog version to %s</info>',
            $version
        ));

        return 0;
    }
}
