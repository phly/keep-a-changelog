<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\ListMilestonesEvent;
use Phly\KeepAChangelog\Milestone\ListMilestonesListener;
use Phly\KeepAChangelog\Provider\Milestone;
use PhlyTest\KeepAChangelog\TestAsset\AbstractMilestoneAwareProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class ListMilestonesListenerTest extends TestCase
{
    private ListMilestonesEvent&MockObject $event;
    private OutputInterface&MockObject $output;
    private AbstractMilestoneAwareProvider&MockObject $provider;

    public function setUp(): void
    {
        $this->event    = $this->createMock(ListMilestonesEvent::class);
        $this->output   = $this->createMock(OutputInterface::class);
        $this->provider = $this->createMock(AbstractMilestoneAwareProvider::class);

        $this->event->expects($this->any())->method('output')->willReturn($this->output);
        $this->event->expects($this->any())->method('provider')->willReturn($this->provider);
        $this->output
            ->expects($this->atLeastOnce())
            ->method('writeln')
            ->with($this->stringContains('Fetching milestones'));
    }

    public function testNotifiesEventWithDiscoveredMilestonesOnSuccess(): void
    {
        $expected = [new Milestone(1, '1.0.0'), new Milestone(2, '1.0.1')];
        $this->provider->expects($this->once())->method('listMilestones')->willReturn($expected);
        $this->event->expects($this->never())->method('errorListingMilestones');
        $this->event->expects($this->once())->method('milestonesRetrieved')->with($expected);

        $listener = new ListMilestonesListener();
        $this->assertNull($listener($this->event));
    }

    public function testNotifiesEventOfErrorsRetrievingMilestones(): void
    {
        $e = new RuntimeException('this is the error message');
        $this->provider->expects($this->once())->method('listMilestones')->willThrowException($e);
        $this->event->expects($this->once())->method('errorListingMilestones')->with($e);
        $this->event->expects($this->never())->method('milestonesRetrieved');

        $listener = new ListMilestonesListener();
        $this->assertNull($listener($this->event));
    }
}
