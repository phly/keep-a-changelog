<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddChangelogEntryEvent extends AbstractEvent
{
    /** @var string */
    private $entry;

    /** @var string */
    private $entryType;

    /** @var null|int */
    private $issueNumber;

    /** @var null|int */
    private $patchNumber;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher,
        string $entryType,
        string $entry,
        ?int $patchNumber,
        ?int $issueNumber
    ) {
        $this->input       = $input;
        $this->output      = $output;
        $this->dispatcher  = $dispatcher;
        $this->entryType   = $entryType;
        $this->entry       = $entry;
        $this->patchNumber = $patchNumber;
        $this->issueNumber = $issueNumber;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function entry() : string
    {
        return $this->entry;
    }

    public function entryType() : string
    {
        return $this->entryType;
    }

    public function issueNumber() : ?int
    {
        return $this->issueNumber;
    }

    public function patchNumber() : ?int
    {
        return $this->patchNumber;
    }

    public function updateEntry(string $entry) : void
    {
        $this->entry = $entry;
    }

    public function addedChangelogEntry() : void
    {
        $this->output->writeln(sprintf(
            '<info>Wrote "%s" entry to %s</info>',
            ucwords($this->entryType),
            $this->changelogFile
        ));
    }

    public function entryIsEmpty() : void
    {
        $this->failed = true;
        $this->output->writeln(
            '<error>The <entry> argument MUST be a non-empty string</error>'
        );
    }

    public function issueNumberIsInvalid(int $issueNumber) : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>The --issue argument (%d) is invalid</error>',
            $issueNumber
        ));
        $this->output->writeln('The value must be numeric, and start with a digit between 1 and 9');
    }

    public function patchNumberIsInvalid(int $patchNumber) : void
    {
        $this->failed = true;
        $this->output->writeln(sprintf(
            '<error>The --pr argument (%d) is invalid</error>',
            $patchNumber
        ));
        $this->output->writeln('The value must be numeric, and start with a digit between 1 and 9');
    }

    public function providerCannotGenerateLinks() : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Cannot generate link to patch or issue</error>');
        $this->output->writeln('In most cases, this is due to a missing package argument.');
    }

    public function issueLinkIsInvalid(string $link) : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Generated issue link is invalid</error>');
        $this->output->writeln(sprintf(
            'The issue identifier provided resulted in the link %s,'
            . ' which does not resolve to a valid location.',
            $link
        ));
    }

    public function patchLinkIsInvalid(string $link) : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Generated patch link is invalid</error>');
        $this->output->writeln(sprintf(
            'The patch identifier provided resulted in the link %s,'
            . ' which does not resolve to a valid location.',
            $link
        ));
    }

    public function entryTypeIsInvalid() : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Entry type is invalid</error>');
        $this->output->writeln(sprintf(
            'You selected to add an entry of type "%s", but only the set [%s] are valid',
            strtolower($this->entryType),
            implode(', ', EntryTypes::TYPES)
        ));
    }

    public function matchingEntryTypeNotFound() : void
    {
        $this->failed = true;
        $this->output->writeln('<error>Unable to find matching entry type in changelog</error>');
        $this->output->writeln(sprintf('The entry type %s could not be found.', $this->entryType));
    }
}
