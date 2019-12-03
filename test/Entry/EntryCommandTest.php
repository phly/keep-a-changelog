<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\EntryCommand;
use Phly\KeepAChangelog\Entry\EntryTypes;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TypeError;

class EntryCommandTest extends TestCase
{
    protected function setUp() : void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
    }

    public function executeCommand(EntryCommand $command) : int
    {
        $r = new ReflectionMethod($command, 'execute');
        $r->setAccessible(true);
        return $r->invoke($command, $this->input->reveal(), $this->output->reveal());
    }

    public function testConstructorRequiresAName()
    {
        $this->expectException(TypeError::class);
        new EntryCommand($this->dispatcher->reveal());
    }

    public function nonNamespacedCommandNames() : iterable
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
        new EntryCommand($this->dispatcher->reveal(), $name);
    }

    public function testConstructorRaisesExceptionWhenNamespacedCommandDoesNotEndInValidType()
    {
        $this->expectException(Exception\InvalidNoteTypeException::class);
        new EntryCommand($this->dispatcher->reveal(), 'command:invalid');
    }

    public function testNonFailureStatusFromExecutionReturnsZero()
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;

        $input->getOption('pr')->willReturn(2);
        $input->getOption('issue')->willReturn(1);
        $input->getArgument('entry')->willReturn('New entry');
        $input->getOption('release-version')->willReturn('1.2.3');

        $expectedEvent = $this->prophesize(AddChangelogEntryEvent::class);
        $expectedEvent->failed()->willReturn(false);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertSame(EntryTypes::TYPE_ADDED, $event->entryType());
                TestCase::assertSame('New entry', $event->entry());
                TestCase::assertSame('1.2.3', $event->version());
                TestCase::assertSame(2, $event->patchNumber());
                TestCase::assertSame(1, $event->issueNumber());
                return $event;
            }))
            ->will(function () use ($expectedEvent) {
                return $expectedEvent->reveal();
            });

        $command = new EntryCommand($dispatcher->reveal(), 'entry:added');

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testFailureStatusFromExecutionReturnsOne()
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;

        $input->getOption('pr')->willReturn(2);
        $input->getOption('issue')->willReturn(1);
        $input->getArgument('entry')->willReturn('New entry');
        $input->getOption('release-version')->willReturn('1.2.3');

        $expectedEvent = $this->prophesize(AddChangelogEntryEvent::class);
        $expectedEvent->failed()->willReturn(true);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertSame(EntryTypes::TYPE_ADDED, $event->entryType());
                TestCase::assertSame('New entry', $event->entry());
                TestCase::assertSame('1.2.3', $event->version());
                TestCase::assertSame(2, $event->patchNumber());
                TestCase::assertSame(1, $event->issueNumber());
                return $event;
            }))
            ->will(function () use ($expectedEvent) {
                return $expectedEvent->reveal();
            });

        $command = new EntryCommand($dispatcher->reveal(), 'entry:added');

        $this->assertSame(1, $this->executeCommand($command));
    }
}
