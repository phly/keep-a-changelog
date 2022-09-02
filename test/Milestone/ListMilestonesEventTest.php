<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\ListMilestonesEvent;
use Phly\KeepAChangelog\Provider\Milestone;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListMilestonesEventTest extends TestCase
{
    use ProphecyTrait;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    /** @var ListMilestonesEvent */
    private $event;

    /** @var InputInterface|ObjectProphecy */
    private $input;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    public function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->event      = new ListMilestonesEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->dispatcher->reveal()
        );
    }

    public function testIndicatingMilestonesWithEmptyArraySendsOutputIndicatingNoneFound(): void
    {
        $this->output->writeln(Argument::containingString('No milestones discovered'))->shouldBeCalled();
        $this->assertNull($this->event->milestonesRetrieved([]));
    }

    public function testIndicatingMilestonesWithArraySendsOutputListingMilestoneData(): void
    {
        $output     = $this->output;
        $milestone1 = new Milestone(1, '1.0.0', '1.0.0 requirements');
        $milestone2 = new Milestone(2, '1.0.1', '1.0.1 requirements');
        $milestone3 = new Milestone(3, '1.1.0', '1.1.0 requirements');
        $milestone4 = new Milestone(4, '2.0.0', '2.0.0 requirements');

        $milestone4Expectation = function () use ($output) {
            $output->writeln(Argument::containingString('- (4) 2.0.0: 2.0.0 requirements'))->shouldBeCalled();
        };

        $milestone3Expectation = function () use ($output, $milestone4Expectation) {
            $output
                ->writeln(Argument::containingString('- (3) 1.1.0: 1.1.0 requirements'))
                ->will($milestone4Expectation)
                ->shouldBeCalled();
        };

        $milestone2Expectation = function () use ($output, $milestone3Expectation) {
            $output
                ->writeln(Argument::containingString('- (2) 1.0.1: 1.0.1 requirements'))
                ->will($milestone3Expectation)
                ->shouldBeCalled();
        };

        $milestone1Expectation = function () use ($output, $milestone2Expectation) {
            $output
                ->writeln(Argument::containingString('- (1) 1.0.0: 1.0.0 requirements'))
                ->will($milestone2Expectation)
                ->shouldBeCalled();
        };

        $output
            ->writeln(Argument::containingString('Found the following milestones'))
            ->will($milestone1Expectation)
            ->shouldBeCalled();

        $this->assertNull($this->event->milestonesRetrieved([
            $milestone1,
            $milestone2,
            $milestone3,
            $milestone4,
        ]));
    }

    public function testIndicatingErrorRetrievingMilestonesSendsOutputAndMarksEventFailed(): void
    {
        $output = $this->output;
        $e      = new RuntimeException('this is the error message');

        $finalExpectation = function () use ($output) {
            $output->writeln(Argument::containingString('Error Message: this is the error message'))->shouldBeCalled();
        };

        $emptyExpectation = function () use ($output, $finalExpectation) {
            $output->writeln('')->will($finalExpectation)->shouldBeCalled();
        };

        $descExpectation = function () use ($output, $emptyExpectation) {
            $output
                ->writeln(Argument::containingString('retrieve milestones'))
                ->will($emptyExpectation)
                ->shouldBeCalled();
        };

        $output
            ->writeln(Argument::containingString('Error listing milestones'))
            ->will($descExpectation)
            ->shouldBeCalled();

        $this->assertNull($this->event->errorListingMilestones($e));
    }

    public function testMilestoneRetrievalErrorDueToAuthenticationProvidesUniqueMessage(): void
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

        $this->assertNull($this->event->errorListingMilestones($e));
        $this->assertTrue($this->event->failed());
    }
}
