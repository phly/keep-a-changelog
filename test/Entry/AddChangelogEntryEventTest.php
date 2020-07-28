<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\EntryTypes;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddChangelogEntryEventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->output->writeln(Argument::type('string'))->willReturn(null);
    }

    public function createEvent(
        string $entryType,
        string $entry,
        ?string $version = null,
        ?int $patchNumber = null,
        ?int $issueNumber = null
    ): AddChangelogEntryEvent {
        return new AddChangelogEntryEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal(),
            $entryType,
            $entry,
            $version,
            $patchNumber,
            $issueNumber
        );
    }

    public function testImplementsPackageEvent(): AddChangelogEntryEvent
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');
        $this->assertInstanceOf(EventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testImplementsChangelogAwareEvent(AddChangelogEntryEvent $event)
    {
        $this->assertInstanceOf(ChangelogEntryAwareEventInterface::class, $event);
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testPropagationIsNotStoppedByDefault(AddChangelogEntryEvent $event)
    {
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @depends testImplementsPackageEvent
     */
    public function testIsNotFailedByDefault(AddChangelogEntryEvent $event)
    {
        $this->assertFalse($event->failed());
    }

    public function testConstructorArgumentsAreAccessible()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog', '1.2.3', 42, 84);

        $this->assertSame($this->input->reveal(), $event->input());
        $this->assertSame($this->output->reveal(), $event->output());
        $this->assertSame($this->dispatcher->reveal(), $event->dispatcher());
        $this->assertSame(EntryTypes::TYPE_ADDED, $event->entryType());
        $this->assertSame('New entry for changelog', $event->entry());
        $this->assertSame('1.2.3', $event->version());
        $this->assertSame(42, $event->patchNumber());
        $this->assertSame(84, $event->issueNumber());
    }

    public function testUpdateEntryResetsEntryInInstance()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $updated = 'UPDATED ENTRY';
        $event->updateEntry($updated);

        $this->assertSame($updated, $event->entry());
    }

    public function testAddingChangelogEntryEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->addedChangelogEntry('CHANGELOG.md', EntryTypes::TYPE_ADDED);

        $this->output
            ->writeln(Argument::containingString('Wrote "Added" entry to CHANGELOG.md'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testMarkingEntryAsEmptyEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, '');

        $event->entryIsEmpty();

        $this->output
            ->writeln(Argument::containingString('MUST be a non-empty string'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidIssueNumberEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->issueNumberIsInvalid(-1);

        $this->output
            ->writeln(Argument::containingString('--issue argument (-1) is invalid'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidPatchNumberEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->patchNumberIsInvalid(-1);

        $this->output
            ->writeln(Argument::containingString('--pr argument (-1) is invalid'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingProviderCannotGenerateLinksEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->providerCannotGenerateLinks();

        $this->output
            ->writeln(Argument::containingString('Cannot generate link to patch or issue'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('missing package argument'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidIssueLinkEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->issueLinkIsInvalid('invalid link');

        $this->output
            ->writeln(Argument::containingString('Generated issue link is invalid'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('link "invalid link"'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidPatchLinkEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->patchLinkIsInvalid('invalid link');

        $this->output
            ->writeln(Argument::containingString('Generated patch link is invalid'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('link "invalid link"'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingEntryTypeIsInvalidEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent('bogus-entry-type', 'New entry for changelog');

        $event->entryTypeIsInvalid();

        $this->output
            ->writeln(Argument::containingString('Entry type is invalid'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('entry of type "bogus-entry-type"'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingEntryTypeNotFoundEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->matchingEntryTypeNotFound();

        $this->output
            ->writeln(Argument::containingString('Unable to find matching entry type'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('entry type "added" could not be found'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
