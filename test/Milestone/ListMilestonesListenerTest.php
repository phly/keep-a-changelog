<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\ListMilestonesEvent;
use Phly\KeepAChangelog\Milestone\ListMilestonesListener;
use Phly\KeepAChangelog\Provider\Milestone;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class ListMilestonesListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ListMilestonesEvent|ObjectProphecy */
    private $event;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    /** @var MilestoneAwareProviderInterface|ObjectProphecy */
    private $provider;

    public function setUp(): void
    {
        $this->event    = $this->prophesize(ListMilestonesEvent::class);
        $this->output   = $this->prophesize(OutputInterface::class);
        $this->provider = $this->prophesize(MilestoneAwareProviderInterface::class)
            ->willImplement(ProviderInterface::class);

        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->provider()->will([$this->provider, 'reveal']);
        $this->output->writeln(Argument::containingString('Fetching milestones'))->shouldBeCalled();
    }

    public function testNotifiesEventWithDiscoveredMilestonesOnSuccess(): void
    {
        $expected = [new Milestone(1, '1.0.0'), new Milestone(2, '1.0.1')];
        $this->provider->listMilestones()->willReturn($expected)->shouldBeCalled();
        $this->event->errorListingMilestones(Argument::any())->shouldNotBeCalled();
        $this->event->milestonesRetrieved($expected)->shouldBeCalled();

        $listener = new ListMilestonesListener();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testNotifiesEventOfErrorsRetrievingMilestones(): void
    {
        $e = new RuntimeException('this is the error message');
        $this->provider->listMilestones()->willThrow($e)->shouldBeCalled();
        $this->event->errorListingMilestones($e)->shouldBeCalled();
        $this->event->milestonesRetrieved(Argument::any())->shouldNotBeCalled();

        $listener = new ListMilestonesListener();
        $this->assertNull($listener($this->event->reveal()));
    }
}
