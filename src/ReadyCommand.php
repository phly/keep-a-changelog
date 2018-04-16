<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadyCommand extends Command
{
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'In the latest changelog entry, mark the entry ready by setting its release date.';

    private const HELP = <<< 'EOH'
In the latest changelog entry, mark the entry ready by setting its release date.

If no --date is specified, the current date in YYYY-MM-DD format will be used.
EOH;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addOption(
            'date',
            '-d',
            InputOption::VALUE_REQUIRED,
            'Specific date string to use; use this if the date is other than today,'
            . ' or if you wish to use a different date format.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $date = $input->getOption('date') ?: date('Y-m-d');

        $output->writeln(sprintf(
            '<info>Setting release date of most recent changelog to "%s"</info>',
            $date
        ));

        $changelogFile = $this->getChangelogFile($input);

        (new SetDate())($changelogFile, $date);

        return 0;
    }
}
