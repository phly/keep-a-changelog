<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\EntryCommand;
use Phly\KeepAChangelog\Entry\EntryTypes;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TypeError;

class EntryCommandTest extends TestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
    }

    public function executeCommand(EntryCommand $command): int
    {
        $r = new ReflectionMethod($command, 'execute');
        $r->setAccessible(true);
        return $r->invoke($command, $this->input, $this->output);
    }

    public function testConstructorRequiresAName()
    {
        $this->expectException(TypeError::class);
        new EntryCommand($this->dispatcher);
    }

    public function nonNamespacedCommandNames(): iterable
    {
        // @phpcs:disable
        return [
            'invalid'               => ['invalid'],
            'known-type-standalone' => [EntryTypes::TYPE_ADDED],
        ];
        // @phpcs:enable
    }

    /**
     * @dataProvider nonNamespacedCommandNames
     */
    public function testConstructorRaisesExceptionForNonNamespacedCommandNames(?string $name)
    {
        $this->expectException(Exception\InvalidNoteTypeException::class);
        new EntryCommand($this->dispatcher, $name);
    }

    public function testConstructorRaisesExceptionWhenNamespacedCommandDoesNotEndInValidType()
    {
        $this->expectException(Exception\InvalidNoteTypeException::class);
        new EntryCommand($this->dispatcher, 'command:invalid');
    }

    public function testNonFailureStatusFromExecutionReturnsZero()
    {
        $this->input->expects($this->once())->method('getArgument')->with('entry')->willReturn('New entry');
        $this->input
            ->expects($this->atLeast(3))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['pr', 2],
                ['issue', 1],
                ['release-version', '1.2.3'],
            ]));

        $expectedEvent = $this->createMock(AddChangelogEntryEvent::class);
        $expectedEvent->expects($this->once())->method('failed')->willReturn(false);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (AddChangelogEntryEvent $event): bool {
                TestCase::assertSame($this->input, $event->input());
                TestCase::assertSame($this->output, $event->output());
                TestCase::assertSame($this->dispatcher, $event->dispatcher());
                TestCase::assertSame(EntryTypes::TYPE_ADDED, $event->entryType());
                TestCase::assertSame('New entry', $event->entry());
                TestCase::assertSame('1.2.3', $event->version());
                TestCase::assertSame(2, $event->patchNumber());
                TestCase::assertSame(1, $event->issueNumber());
                return true;
            }))
            ->willReturn($expectedEvent);

        $command = new EntryCommand($this->dispatcher, 'entry:added');

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testFailureStatusFromExecutionReturnsOne()
    {
        $this->input->expects($this->once())->method('getArgument')->with('entry')->willReturn('New entry');
        $this->input
            ->expects($this->atLeast(3))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['pr', 2],
                ['issue', 1],
                ['release-version', '1.2.3'],
            ]));

        $expectedEvent = $this->createMock(AddChangelogEntryEvent::class);
        $expectedEvent->expects($this->once())->method('failed')->willReturn(true);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (AddChangelogEntryEvent $event): bool {
                TestCase::assertSame($this->input, $event->input());
                TestCase::assertSame($this->output, $event->output());
                TestCase::assertSame($this->dispatcher, $event->dispatcher());
                TestCase::assertSame(EntryTypes::TYPE_ADDED, $event->entryType());
                TestCase::assertSame('New entry', $event->entry());
                TestCase::assertSame('1.2.3', $event->version());
                TestCase::assertSame(2, $event->patchNumber());
                TestCase::assertSame(1, $event->issueNumber());
                return true;
            }))
            ->willReturn($expectedEvent);

        $command = new EntryCommand($this->dispatcher, 'entry:added');

        $this->assertSame(1, $this->executeCommand($command));
    }
}
