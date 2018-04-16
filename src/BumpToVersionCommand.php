<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add a new changelog entry using the version specified.
 */
class BumpToVersionCommand extends Command
{
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'Create a new changelog entry for the specified release version.';

    private const HELP = <<< 'EOH'
Add a new release entry to the changelog, based on the latest release specified.

EOH;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'Version to use with newly created changelog entry.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $changelogFile = $this->getChangelogFile($input);
        if (! is_readable($changelogFile)) {
            throw Exception\ChangelogFileNotFoundException::at($changelogFile);
        }

        $version = $input->getArgument('version');

        $bumper = new ChangelogBump($changelogFile);
        $bumper->updateChangelog($version);

        $output->writeln(sprintf(
            '<info>Added changelog entry for version %s</info>',
            $version
        ));

        return 0;
    }
}
