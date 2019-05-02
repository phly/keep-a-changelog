<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowVersionCommand extends Command
{
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'Show the changelog entry for the given version.';

    private const HELP = <<< 'EOH'
Opens the changelog and displays the entry for the given version.
EOH;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'Which version do you wish to display?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $version = $input->getArgument('version');

        if (! preg_match('/^\d+\.\d+\.\d+/', $version)) {
            $output->writeln('Version provided is not a semantic version; please check and retry.');
            return 1;
        }

        $changelogFile = $this->getChangelogFile($input);
        $changelogs    = file_get_contents($changelogFile);
        $parser        = new ChangelogParser();

        $releaseDate   = $parser->findReleaseDateForVersion($changelogs, $version);
        $changelog     = $parser->findChangelogForVersion($changelogs, $version);

        $output->writeln(sprintf(
            '<info>Showing changelog for version %s (released %s):</info>',
            $version,
            $releaseDate
        ));
        $output->writeln('');
        $output->write($changelog);
        $output->writeln('');

        return 0;
    }
}
