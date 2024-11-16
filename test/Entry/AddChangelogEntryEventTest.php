<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\EntryTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddChangelogEntryEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function createEvent(
        string $entryType,
        string $entry,
        ?string $version = null,
        ?int $patchNumber = null,
        ?int $issueNumber = null
    ): AddChangelogEntryEvent {
        return new AddChangelogEntryEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
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

        $this->assertSame($this->input, $event->input());
        $this->assertSame($this->output, $event->output());
        $this->assertSame($this->dispatcher, $event->dispatcher());
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
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Wrote "Added" entry to CHANGELOG.md'));

        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->addedChangelogEntry('CHANGELOG.md', EntryTypes::TYPE_ADDED);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testMarkingEntryAsEmptyEmitsOutputAndStopsPropagationWithFailure()
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('MUST be a non-empty string'));

        $event = $this->createEvent(EntryTypes::TYPE_ADDED, '');

        $event->entryIsEmpty();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidIssueNumberEmitsOutputAndStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('--issue argument (-1) is invalid', $message),
                    2       => TestCase::assertStringContainsString('The value must be numeric, and start with a digit between 1 and 9', $message),
                    default => true,
                };

                return true;
            }));


        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->issueNumberIsInvalid(-1);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidPatchNumberEmitsOutputAndStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('--pr argument (-1) is invalid', $message),
                    2       => TestCase::assertStringContainsString('The value must be numeric, and start with a digit between 1 and 9', $message),
                    default => true,
                };

                return true;
            }));


        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->patchNumberIsInvalid(-1);

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingProviderCannotGenerateLinksEmitsOutputAndStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Cannot generate link to patch or issue', $message),
                    2       => TestCase::assertStringContainsString('missing package argument', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->providerCannotGenerateLinks();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidIssueLinkEmitsOutputAndStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Generated issue link is invalid', $message),
                    2       => TestCase::assertStringContainsString('link "invalid link"', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->issueLinkIsInvalid('invalid link');

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingInvalidPatchLinkEmitsOutputAndStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Generated patch link is invalid', $message),
                    2       => TestCase::assertStringContainsString('link "invalid link"', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->patchLinkIsInvalid('invalid link');

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingEntryTypeIsInvalidEmitsOutputAndStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Entry type is invalid', $message),
                    2       => TestCase::assertStringContainsString('entry of type "bogus-entry-type"', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent('bogus-entry-type', 'New entry for changelog');

        $event->entryTypeIsInvalid();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testIndicatingEntryTypeNotFoundEmitsOutputAndStopsPropagationWithFailure()
    {
        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Unable to find matching entry type', $message),
                    2       => TestCase::assertStringContainsString('entry type "added" could not be found', $message),
                    default => true,
                };

                return true;
            }));

        $event = $this->createEvent(EntryTypes::TYPE_ADDED, 'New entry for changelog');

        $event->matchingEntryTypeNotFound();

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
