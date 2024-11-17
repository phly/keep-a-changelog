<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\ListMilestonesEvent;
use Phly\KeepAChangelog\Provider\Milestone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListMilestonesEventTest extends TestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private ListMilestonesEvent $event;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    public function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->event      = new ListMilestonesEvent($this->input, $this->output, $this->dispatcher);
    }

    public function testIndicatingMilestonesWithEmptyArraySendsOutputIndicatingNoneFound(): void
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('No milestones discovered'));
        $this->assertNull($this->event->milestonesRetrieved([]));
    }

    public function testIndicatingMilestonesWithArraySendsOutputListingMilestoneData(): void
    {
        $milestone1 = new Milestone(1, '1.0.0', '1.0.0 requirements');
        $milestone2 = new Milestone(2, '1.0.1', '1.0.1 requirements');
        $milestone3 = new Milestone(3, '1.1.0', '1.1.0 requirements');
        $milestone4 = new Milestone(4, '2.0.0', '2.0.0 requirements');

        $invokedCount = $this->atLeast(5);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Found the following milestones', $message),
                    2       => TestCase::assertStringContainsString('- (1) 1.0.0: 1.0.0 requirements', $message),
                    3       => TestCase::assertStringContainsString('- (2) 1.0.1: 1.0.1 requirements', $message),
                    4       => TestCase::assertStringContainsString('- (3) 1.1.0: 1.1.0 requirements', $message),
                    5       => TestCase::assertStringContainsString('- (4) 2.0.0: 2.0.0 requirements', $message),
                    default => true,
                };

                return true;
            }));

        $this->assertNull($this->event->milestonesRetrieved([
            $milestone1,
            $milestone2,
            $milestone3,
            $milestone4,
        ]));
    }

    public function testIndicatingErrorRetrievingMilestonesSendsOutputAndMarksEventFailed(): void
    {
        $e = new RuntimeException('this is the error message');

        $invokedCount = $this->atLeast(4);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Error listing milestones', $message),
                    2       => TestCase::assertStringContainsString('retrieve milestones', $message),
                    3       => TestCase::assertStringContainsString('', $message),
                    4       => TestCase::assertStringContainsString(
                        'Error Message: this is the error message',
                        $message
                    ),
                    default => true,
                };

                return true;
            }));

        $this->assertNull($this->event->errorListingMilestones($e));
    }

    public function testMilestoneRetrievalErrorDueToAuthenticationProvidesUniqueMessage(): void
    {
        $e = new RuntimeException('this is the error message', 401);

        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString(
                        'Invalid credentials',
                        $message
                    ),
                    2       => TestCase::assertStringContainsString(
                        'The credentials associated with your Git provider are invalid',
                        $message
                    ),
                    default => true,
                };

                return true;
            }));

        $this->assertNull($this->event->errorListingMilestones($e));
        $this->assertTrue($this->event->failed());
    }
}
