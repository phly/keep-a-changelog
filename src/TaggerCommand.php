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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaggerCommand extends Command
{
    use GetChangelogFileTrait;

    private const HELP = <<< 'EOH'
Create a new git tag for the current repository, using the relevant changelog entry.

Parses the CHANGELOG.md file and extracts the entry matching <version>; if no
matching version is found, or the entry does not have a date set, the tool will
raise an error.

Once extracted, the command runs "git tag -s <tagname>" using the following
message format:

    <package> <version>

    <changelog>

By default, the tool assumes that the current working directory is the package
name; if this is not the case, provide that optional argument when invoking the
tool.

EOH;

    protected function configure() : void
    {
        $this->setDescription('Create a new tag, using the relevant changelog entry.');
        $this->setHelp(self::HELP);
        $this->addArgument('version', InputArgument::REQUIRED, 'Version to tag');
        $this->addOption(
            'package',
            'p',
            InputOption::VALUE_REQUIRED,
            'Package name; defaults to name of working directory'
        );
        $this->addOption(
            'tagname',
            'a',
            InputOption::VALUE_REQUIRED,
            'Alternate git tag name to use when tagging; defaults to <version>'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $cwd = realpath(getcwd());

        $version = $input->getArgument('version');
        $package = $input->getOption('package') ?: basename($cwd);
        $tagName = $input->getOption('tagname') ?: $version;

        $changelogFile = $this->getChangelogFile($input);
        if (! is_readable($changelogFile)) {
            throw Exception\ChangelogFileNotFoundException::at($changelogFile);
        }

        $parser = new ChangelogParser();
        $changelog = $parser->findChangelogForVersion(
            file_get_contents($changelogFile),
            $version
        );

        $formatter = new ChangelogFormatter();
        $changelog = $formatter->format($changelog);

        if (! $this->tagWithChangelog($tagName, $package, $version, $changelog)) {
            $output->writeln('<error>Error creating tag!</error>');
            $output->writeln('Check the output logs for details');
            return 1;
        }

        $output->writeln(sprintf(
            '<info>Created tag "%s" for package "%s" using the following notes:</info>',
            $version,
            $package
        ));

        $output->write($changelog);

        return 0;
    }

    private function tagWithChangelog(string $tagName, string $package, string $version, string $changelog) : bool
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($tempFile, sprintf("%s %s\n\n%s", $package, $version, $changelog));

        $command = sprintf('git tag -s -F %s %s', $tempFile, $tagName);
        system($command, $return);

        unlink($tempFile);

        return 0 === $return;
    }
}
