<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CloseMilestoneEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseMilestoneEventTest extends TestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
    }

    public function testConstructorPullsIdentifierFromInputArgument(): void
    {
        $this->input->expects($this->once())->method('getArgument')->with('id')->willReturn(200);
        $event = new CloseMilestoneEvent($this->input, $this->output, $this->dispatcher);

        $this->assertSame(200, $event->id());
    }

    public function testClosingMilestoneEmitsOutput(): void
    {
        $this->input->expects($this->once())->method('getArgument')->with('id')->willReturn(200);
        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('Closed milestone 200'));

        $event = new CloseMilestoneEvent($this->input, $this->output, $this->dispatcher);

        $this->assertNull($event->milestoneClosed());
    }

    public function testIndicatingErrorEmitsOutputAndFailsEvent(): void
    {
        $e = new RuntimeException('this is the error message');

        $this->input->expects($this->once())->method('getArgument')->with('id')->willReturn(200);

        $invokedCount = $this->atLeast(4);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Error closing milestone', $message),
                    2       => TestCase::assertStringContainsString('close the milestone', $message),
                    3       => TestCase::assertSame('', $message),
                    4       => TestCase::assertStringContainsString('this is the error message', $message),
                    default => true,
                };

                return true;
            }));

        $event = new CloseMilestoneEvent($this->input, $this->output, $this->dispatcher);
        $this->assertNull($event->errorClosingMilestone($e));
    }

    public function testMilestoneCloseErrorDueToAuthenticationProvidesUniqueMessage(): void
    {
        $e = new RuntimeException('this is the error message', 401);

        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Invalid credentials', $message),
                    2       => TestCase::assertStringContainsString('The credentials associated with your Git provider are invalid', $message),
                    default => true,
                };

                return true;
            }));

        $event = new CloseMilestoneEvent($this->input, $this->output, $this->dispatcher);

        $this->assertNull($event->errorClosingMilestone($e));
        $this->assertTrue($event->failed());
    }
}
