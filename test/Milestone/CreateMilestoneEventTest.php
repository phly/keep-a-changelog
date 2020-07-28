<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use Phly\KeepAChangelog\Provider\Milestone;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMilestoneEventTest extends TestCase
{
    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    /** @var CreateMilestoneEvent */
    private $event;

    /** @var InputInterface|ObjectProphecy */
    private $input;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    public function setUp(): void
    {
        $this->input = $this->prophesize(InputInterface::class);
        $this->input->getArgument('title')->willReturn('2.0.0');
        $this->input->getArgument('description')->willReturn('2.0.0 requirements');

        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->event = new CreateMilestoneEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal()
        );
    }

    public function testUsesTitleArgumentFromInput(): void
    {
        $this->assertSame('2.0.0', $this->event->title());
    }

    public function testUsesDescriptionArgumentFromInput(): void
    {
        $this->assertSame('2.0.0 requirements', $this->event->description());
    }

    public function testMarkingMilestoneCreatedSendsOutput(): void
    {
        $this->output
            ->writeln(Argument::containingString('Created milestone (1234) 2.0.0: 2.0.0 requirements'))
            ->shouldBeCalled();
        $milestone = new Milestone(1234, '2.0.0', '2.0.0 requirements');

        $this->assertNull($this->event->milestoneCreated($milestone));
        $this->assertFalse($this->event->failed());
    }

    public function testIndicatingMilestoneCreationErrorMarksEventFailedAndSendsOutput(): void
    {
        $e = new RuntimeException('this is the error message');

        $output = $this->output;
        $output
            ->writeln(Argument::containingString('Error creating milestone'))
            ->will(function () use ($output) {
                $output
                    ->writeln(Argument::containingString('An error occurred when attempting to create the milestone'))
                    ->will(function () use ($output) {
                        $output
                            ->writeln('')
                            ->will(function () use ($output) {
                                $output
                                    ->writeln(Argument::containingString('Error Message: this is the error message'))
                                    ->shouldBeCalled();
                            })
                            ->shouldBeCalled();
                    })
                    ->shouldBeCalled();
            })
            ->shouldBeCalled();

        $this->assertNull($this->event->errorCreatingMilestone($e));
        $this->assertTrue($this->event->failed());
    }
}
