<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpToVersionCommand;
use Phly\KeepAChangelog\Exception;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpToVersionCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    public function setUp()
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function testExecutionReturnsZeroOnSuccess()
    {
        $input      = $this->input;
        $input->getArgument('version')->willReturn('1.2.3');
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(false);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertNull($event->bumpMethod());
                TestCase::assertSame('1.2.3', $event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpToVersionCommand($this->dispatcher->reveal());

        $this->assertSame(0, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneOnFailure()
    {
        $input      = $this->input;
        $input->getArgument('version')->willReturn('1.2.3');
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(true);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertNull($event->bumpMethod());
                TestCase::assertSame('1.2.3', $event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpToVersionCommand($this->dispatcher->reveal());

        $this->assertSame(1, $this->executeCommand($command));
    }
}
