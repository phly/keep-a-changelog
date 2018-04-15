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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommand extends Command
{
    private const DESCRIPTION = 'Edit the latest changelog entry using the system editor.';

    private const HELP = <<< 'EOH'
Edit the latest changelog entry using the system editor ($EDITOR), or the
editor provided via --editor.

By default, the command will edit CHANGELOG.md in the current directory, unless
a different file is specified via the --file option.
EOH;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addOption(
            'editor',
            '-e',
            InputOption::VALUE_REQUIRED,
            'Alternate editor command to use to edit the changelog.'
        );
        $this->addOption(
            'file',
            '-f',
            InputOption::VALUE_REQUIRED,
            'Alternate changelog file to edit.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $editor = $input->getOption('editor') ?: null;
        $changelogFile = $input->getOption('file') ?: realpath(getcwd()) . '/CHANGELOG.md';

        if (! (new Edit())($output, $changelogFile, $editor)) {
            $output->writeln(sprintf(
                '<error>Could not edit %s; please check the output for details.</error>',
                $changelogFile
            ));
            return 1;
        }

        $output->writeln(sprintf(
            '<info>Edited most recent changelog in %s</info>',
            $changelogFile
        ));

        return 0;
    }
}
