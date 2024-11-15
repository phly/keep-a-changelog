<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use Phly\KeepAChangelog\Provider\Milestone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMilestoneEventTest extends TestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private CreateMilestoneEvent $event;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    public function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->input->expects($this->any())->method('getArgument')->will($this->returnValueMap([
            ['title', '2.0.0'],
            ['description', '2.0.0 requirements'],
        ]));

        $this->event = new CreateMilestoneEvent($this->input, $this->output, $this->dispatcher);
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
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Created milestone (1234) 2.0.0: 2.0.0 requirements'));

        $milestone = new Milestone(1234, '2.0.0', '2.0.0 requirements');

        $this->assertNull($this->event->milestoneCreated($milestone));
        $this->assertFalse($this->event->failed());
    }

    public function testIndicatingMilestoneCreationErrorMarksEventFailedAndSendsOutput(): void
    {
        $e = new RuntimeException('this is the error message');

        $invokedCount = $this->atLeast(4);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Error creating milestone', $message),
                    2       => TestCase::assertStringContainsString('An error occurred when attempting to create the milestone', $message),
                    3       => TestCase::assertSame('', $message),
                    4       => TestCase::assertStringContainsString('Error Message: this is the error message', $message),
                    default => true,
                };
                return true;
            }));

        $this->assertNull($this->event->errorCreatingMilestone($e));
        $this->assertTrue($this->event->failed());
    }

    public function testMilestoneCreationErrorDueToAuthenticationProvidesUniqueMessage(): void
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

        $this->assertNull($this->event->errorCreatingMilestone($e));
        $this->assertTrue($this->event->failed());
    }
}
