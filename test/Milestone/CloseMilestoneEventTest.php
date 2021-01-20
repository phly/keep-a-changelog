<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CloseMilestoneEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseMilestoneEventTest extends TestCase
{
    use ProphecyTrait;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    /** @var InputInterface|ObjectProphecy */
    private $input;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    public function setUp(): void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
    }

    public function testConstructorPullsIdentifierFromInputArgument(): void
    {
        $this->input->getArgument('id')->willReturn(200)->shouldBeCalled();
        $event = new CloseMilestoneEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal()
        );

        $this->assertSame(200, $event->id());
    }

    public function testClosingMilestoneEmitsOutput(): void
    {
        $this->input->getArgument('id')->willReturn(200)->shouldBeCalled();
        $this->output->writeln(Argument::containingString('Closed milestone 200'))->shouldBeCalled();

        $event = new CloseMilestoneEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal()
        );

        $this->assertNull($event->milestoneClosed());
    }

    public function testIndicatingErrorEmitsOutputAndFailsEvent(): void
    {
        $e      = new RuntimeException('this is the error message');
        $output = $this->output;

        $this->input->getArgument('id')->willReturn(200)->shouldBeCalled();

        $lastOutput = function () use ($output) {
            $output->writeln(Argument::containingString('this is the error message'))->shouldBeCalled();
        };

        $emptyOutput = function () use ($output, $lastOutput) {
            $output
                ->writeln('')
                ->will($lastOutput)
                ->shouldBeCalled();
        };

        $descOutput = function () use ($output, $emptyOutput) {
            $output
                ->writeln(Argument::containingString('close the milestone'))
                ->will($emptyOutput)
                ->shouldBeCalled();
        };

        $output
            ->writeln(Argument::containingString('Error closing milestone'))
            ->will($descOutput)
            ->shouldBeCalled();

        $event = new CloseMilestoneEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal()
        );

        $this->assertNull($event->errorClosingMilestone($e));
    }

    public function testMilestoneCloseErrorDueToAuthenticationProvidesUniqueMessage(): void
    {
        $e = new RuntimeException('this is the error message', 401);

        $output = $this->output;
        $output
            ->writeln(Argument::containingString('Invalid credentials'))
            ->will(function () use ($output) {
                $output
                    ->writeln(Argument::containingString(
                        'The credentials associated with your Git provider are invalid'
                    ))
                    ->shouldBeCalled();
            })
            ->shouldBeCalled();

        $event = new CloseMilestoneEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal()
        );

        $this->assertNull($event->errorClosingMilestone($e));
        $this->assertTrue($event->failed());
    }
}
