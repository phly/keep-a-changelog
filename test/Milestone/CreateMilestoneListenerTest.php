<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use Phly\KeepAChangelog\Milestone\CreateMilestoneListener;
use Phly\KeepAChangelog\Provider\Milestone;
use PhlyTest\KeepAChangelog\TestAsset\AbstractMilestoneAwareProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMilestoneListenerTest extends TestCase
{
    private CreateMilestoneEvent&MockObject $event;
    private OutputInterface&MockObject $output;
    private AbstractMilestoneAwareProvider&MockObject $provider;

    public function setUp(): void
    {
        $this->provider = $this->createMock(AbstractMilestoneAwareProvider::class);
        $this->output   = $this->createMock(OutputInterface::class);
        $this->event    = $this->createMock(CreateMilestoneEvent::class);

        $this->event->expects($this->atLeastOnce())->method('title')->willReturn('2.0.0');
        $this->event->expects($this->atLeastOnce())->method('description')->willReturn('2.0.0 requirements');
        $this->event->expects($this->any())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->any())->method('output')->willReturn($this->output);
    }

    public function testListenerInformsEventWhenMilestoneIsCreated(): void
    {
        $milestone = $this->createMock(Milestone::class);

        $this->provider
            ->expects($this->once())
            ->method('createMilestone')
            ->with('2.0.0', '2.0.0 requirements')
            ->willReturn($milestone);
        $this->event->expects($this->once())->method('milestoneCreated')->with($milestone);
        $this->event->expects($this->never())->method('errorCreatingMilestone');

        $listener = new CreateMilestoneListener();

        $this->assertNull($listener($this->event));
    }

    public function testListenerInformsEventOfMilestoneCreationError(): void
    {
        $e = new RuntimeException('this is the error');
        $this->provider
            ->expects($this->once())
            ->method('createMilestone')
            ->with('2.0.0', '2.0.0 requirements')
            ->willThrowException($e);
        $this->event->expects($this->never())->method('milestoneCreated');
        $this->event->expects($this->once())->method('errorCreatingMilestone')->with($e);

        $listener = new CreateMilestoneListener();

        $this->assertNull($listener($this->event));
    }
}
