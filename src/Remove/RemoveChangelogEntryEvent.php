<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Remove;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\VersionAwareEventInterface;
use Phly\KeepAChangelog\Common\VersionValidationTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class RemoveChangelogEntryEvent extends AbstractEvent implements
    ChangelogEntryAwareEventInterface,
    VersionAwareEventInterface
{
    use VersionValidationTrait;

    /** @var bool */
    private $aborted = false;

    /** @var null|ChangelogEntry */
    private $changelogEntry;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        string $version
    ) {
        $this->input   = $input;
        $this->output  = $output;
        $this->version = $version;
    }

    public function isPropagationStopped() : bool
    {
        return $this->aborted || $this->failed;
    }

    public function changelogEntryNotFound(string $changelogFile, string $version) : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>Could not locate version %s in changelog file %s;'
                . ' please verify the version and/or changelog file.</error>',
            $this->version,
            $this->changelogFile
        ));
    }

    public function discoveredChangelogEntry(ChangelogEntry $entry) : void
    {
        $this->changelogEntry = $entry;
    }

    public function changelogEntry() : ?ChangelogEntry
    {
        return $this->changelogEntry;
    }

    public function abort()
    {
        $this->aborted = true;
        $this->output->writeln('<info>Aborting at user request</info>');
    }

    public function entryRemoved()
    {
        $this->output->writeln(sprintf(
            '<info>Removed changelog version %s from file %s.</info>',
            $this->version,
            $this->changelogFile
        ));
    }
}
