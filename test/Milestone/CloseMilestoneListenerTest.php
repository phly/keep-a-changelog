<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Closure;
use Phly\KeepAChangelog\Milestone\CloseMilestoneEvent;
use Phly\KeepAChangelog\Milestone\CloseMilestoneListener;
use Phly\KeepAChangelog\Provider\Milestone;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PhlyTest\KeepAChangelog\TestAsset\AbstractMilestoneAwareProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class CloseMilestoneListenerTest extends TestCase
{
    private CloseMilestoneEvent&MockObject $event;
    private OutputInterface&MockObject $output;
    private MilestoneAwareProviderInterface&ProviderInterface&MockObject $provider;

    public function setUp(): void
    {
        $this->event    = $this->createMock(CloseMilestoneEvent::class);
        $this->output   = $this->createMock(OutputInterface::class);
        $this->provider = $this->createMock(AbstractMilestoneAwareProvider::class);

        $this->event->expects($this->any())->method('id')->willReturn(200);
        $this->event->expects($this->any())->method('output')->willReturn($this->output);
        $this->event->expects($this->any())->method('provider')->willReturn($this->provider);

        $this->output->expects($this->any())->method('writeln')->with($this->stringContains('Closing milestone'));
    }

    public function testClosingMilestoneNotifiesEvent(): void
    {
        $this->provider->expects($this->once())->method('closeMilestone')->with(200)->willReturn(true);
        $this->event->expects($this->never())->method('errorClosingMilestone');
        $this->event->expects($this->once())->method('milestoneClosed');

        $listener = new CloseMilestoneListener();

        $this->assertNull($listener($this->event));
    }

    public function testErrorClosingMilestoneNotifiesEvent(): void
    {
        $e = new RuntimeException('this is the error message');
        $this->provider->expects($this->once())->method('closeMilestone')->with(200)->willThrowException($e);
        $this->event->expects($this->once())->method('errorClosingMilestone')->with($e);
        $this->event->expects($this->never())->method('milestoneClosed');

        $listener = new CloseMilestoneListener();

        $this->assertNull($listener($this->event));
    }
}
