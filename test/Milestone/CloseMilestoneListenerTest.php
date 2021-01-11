<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CloseMilestoneEvent;
use Phly\KeepAChangelog\Milestone\CloseMilestoneListener;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class CloseMilestoneListenerTest extends TestCase
{
    use ProphecyTrait;

    /** @var CloseMilestoneEvent|ObjectProphecy */
    private $event;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    /** @var MilestoneAwareProviderInterface|ObjectProphecy */
    private $provider;

    public function setUp(): void
    {
        $this->event    = $this->prophesize(CloseMilestoneEvent::class);
        $this->output   = $this->prophesize(OutputInterface::class);
        $this->provider = $this->prophesize(MilestoneAwareProviderInterface::class)
            ->willImplement(ProviderInterface::class);

        $this->event->id()->willReturn(200);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->provider()->will([$this->provider, 'reveal']);

        $this->output->writeln(Argument::containingString('Closing milestone'))->shouldBeCalled();
    }

    public function testClosingMilestoneNotifiesEvent(): void
    {
        $this->provider->closeMilestone(200)->willReturn(true);
        $this->event->errorClosingMilestone(Argument::any())->shouldNotBeCalled();
        $this->event->milestoneClosed()->shouldBeCalled();

        $listener = new CloseMilestoneListener();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testErrorClosingMilestoneNotifiesEvent(): void
    {
        $e = new RuntimeException('this is the error message');
        $this->provider->closeMilestone(200)->willThrow($e);
        $this->event->errorClosingMilestone($e)->shouldBeCalled();
        $this->event->milestoneClosed()->shouldNotBeCalled();

        $listener = new CloseMilestoneListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
