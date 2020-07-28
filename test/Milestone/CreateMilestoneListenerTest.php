<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CreateMilestoneEvent;
use Phly\KeepAChangelog\Milestone\CreateMilestoneListener;
use Phly\KeepAChangelog\Provider\Milestone;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMilestoneListenerTest extends TestCase
{
    /** @var CreateMilestoneEvent|ObjectProphecy */
    private $event;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    /** @var MilestoneAwareProviderInterface|ObjectProphecy */
    private $provider;

    public function setUp() : void
    {
        $this->provider = $this->prophesize(MilestoneAwareProviderInterface::class)
            ->willImplement(ProviderInterface::class);

        $this->output = $this->prophesize(OutputInterface::class);

        $this->event = $this->prophesize(CreateMilestoneEvent::class);
        $this->event->title()->willReturn('2.0.0');
        $this->event->description()->willReturn('2.0.0 requirements');
        $this->event->provider()->will([$this->provider, 'reveal']);
        $this->event->output()->will([$this->output, 'reveal']);
    }

    public function testListenerInformsEventWhenMilestoneIsCreated() : void
    {
        $milestone = $this->prophesize(Milestone::class)->reveal();
        $this->provider->createMilestone('2.0.0', '2.0.0 requirements')->willReturn($milestone)->shouldBeCalled();
        $this->event->milestoneCreated($milestone)->shouldBeCalled();
        $this->event->errorCreatingMilestone(Argument::any())->shouldNotBeCalled();

        $listener = new CreateMilestoneListener();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerInformsEventOfMilestoneCreationError() : void
    {
        $e = new RuntimeException('this is the error');
        $this->provider->createMilestone('2.0.0', '2.0.0 requirements')->willThrow($e)->shouldBeCalled();
        $this->event->milestoneCreated(Argument::any())->shouldNotBeCalled();
        $this->event->errorCreatingMilestone($e)->shouldBeCalled();

        $listener = new CreateMilestoneListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
