<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RemoveCommand extends Command
{
    use ChangelogEditorTrait;
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'Remove a changelog release entry.';

    private const HELP = <<< 'EOH'
Remove the given changelog release entry based on the <version> provided.
The command will provide a preview, and prompt for confirmation before doing
so (unless using the --force-removal flag).
EOH;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addArgument(
            'version',
            InputArgument::REQUIRED,
            'The changelog version to remove.'
        );
        $this->addOption(
            'force-removal',
            'r',
            InputOption::VALUE_NONE,
            'Do not prompt for confirmation.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // Verify we have a version argument we can work with
        $version = $input->getArgument('version') ?: '';
        if (! $this->validateVersion($version, $output)) {
            return 1;
        }

        // Verify we can find the entry in the changelog file
        $changelogFile = $this->getChangelogFile($input);
        $entry         = $this->getChangelogEntry($changelogFile, $version);
        if (! $entry) {
            $output->writeln(sprintf(
                '<error>Could not locate version %s in changelog file %s;'
                . ' please verify the version and/or changelog file.</error>',
                $version,
                $changelogFile
            ));
            return 1;
        }

        // Have the user verify they want to remove the entry
        if (! $input->getOption('force-removal')) {
            $continue = $this->promptForConfirmation($input, $output, $entry);
            if (! $continue) {
                $output->writeln('<info>Aborting at user request</info>');
                return 0;
            }
        }

        if (! (new Remove())($output, $changelogFile, $version)) {
            $output->writeln(sprintf(
                '<error>Could not remove version %s from changelog file %s;'
                . ' please check the output for details.</error>',
                $version,
                $changelogFile
            ));
            return 1;
        }

        $output->writeln(sprintf(
            '<info>Removed changelog version %s from file %s.</info>',
            $version,
            $changelogFile
        ));

        return 0;
    }

    private function validateVersion(string $version, OutputInterface $output) : bool
    {
        if (! preg_match('/^\d+\.\d+\.\d+((?:alpha|a|beta|b|rc|dev)\d+)?$/i', $version)) {
            $output->writeln(sprintf(
                '<error>Invalid version "%s"; must follow semantic versioning rules</error>',
                $version
            ));
            return false;
        }

        return true;
    }

    private function promptForConfirmation(
        InputInterface $input,
        OutputInterface $output,
        stdClass $entry
    ) : bool {
        $output->writeln('<info>Found the following entry:</info>');
        $output->writeln($entry->contents);

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you really want to delete this entry?', false);

        return $helper->ask($input, $output, $question);
    }
}
