<?php
/**
 * @see       https://github.com/phly/keep-a-changelog-tagger for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog-tagger/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntryCommand extends Command
{
    private const DESC_TEMPLATE = 'Create a new changelog entry for the latest changelog in the "%s" section';

    private const HELP_TEMPLATE = <<< 'EOH'
In the latest changelog entry, add the given entry in the section marked
"%s".

If the first entry in that section matches "- Nothing", that line will
be replaced with the new entry.

When the --pr option is provided, the entry will be prepended with a link
to the given pull request. If no --package option is present, we will
attempt to determine the package name from the composer.json file.
EOH;

    /** @var string */
    private $type;

    public function __construct(string $name)
    {
        if (false === strpos($name, ':')) {
            throw Exception\InvalidNoteTypeException::forCommandName($name);
        }

        [$initial, $type] = explode(':', $name, 2);
        if (! in_array($type, AddEntry::TYPES, true)) {
            throw Exception\InvalidNoteTypeException::forCommandName($name);
        }

        $this->type = $type;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription(sprintf(
            self::DESC_TEMPLATE,
            ucwords($this->type)
        ));
        $this->setHelp(sprintf(
            self::HELP_TEMPLATE,
            ucwords($this->type)
        ));
        $this->addArgument(
            'entry',
            InputArgument::REQUIRED,
            'Entry to add to the changelog'
        );
        $this->addOption(
            'pr',
            null,
            InputOption::VALUE_REQUIRED,
            'Pull request number to associate with entry'
        );
        $this->addOption(
            'package',
            null,
            InputOption::VALUE_REQUIRED,
            'Name of package in organization/repo format (for building link to a pull request)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln(sprintf(
            '<info>Preparing entry fro %s section</info>',
            ucwords($this->type)
        ));

        $entry = $this->prepareEntry($input);
        $changelog = sprintf('%s/CHANGELOG.md', realpath(getcwd()));

        $output->writeln(sprintf(
            '<info>Writing "%s" entry to %s</info>',
            ucwords($this->type),
            $changelog
        ));

        (new AddEntry())(
            $this->type,
            $changelog,
            $entry
        );

        return 0;
    }

    /**
     * @throws Exception\EmptyEntryException
     */
    private function prepareEntry(InputInterface $input) : string
    {
        $entry = $input->getArgument('entry');
        if (empty($entry)) {
            throw Exception\EmptyEntryException::create();
        }

        $pr = $input->getOption('pr');
        if (! $pr) {
            return $entry;
        }

        if (! preg_match('/^[1-9]\d*$/', (string) $pr)) {
            throw Exception\InvalidPullRequestException::for($pr);
        }

        return sprintf(
            '[#%d](%s) %s',
            (int) $pr,
            $this->preparePullRequestLink((int) $pr, $input->getOption('package')),
            $entry
        );
    }

    private function preparePullRequestLink(int $pr, ?string $package) : string
    {
        $package = $package ?: (new ComposerPackage())->getName(realpath(getcwd()));

        if (! preg_match('#^[a-z0-9]+[a-z0-9_-]*/[a-z0-9]+[a-z0-9_-]*$#i', $package)) {
            throw Exception\InvalidPackageNameException::forPackage($package);
        }

        return sprintf(
            'https://github.com/%s/pull/%d',
            $package,
            $pr
        );
    }
}
