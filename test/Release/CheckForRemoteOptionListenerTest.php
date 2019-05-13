<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\CheckForRemoteOptionListener;
use Phly\KeepAChangelog\Release\PushTagEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;

class CheckForRemoteOptionListenerTest extends TestCase
{
    public function setUp()
    {
        $this->input    = $this->prophesize(InputInterface::class);
        $this->event    = $this->prophesize(PushTagEvent::class);
    }

    public function testListenerReturnsEarlyIfRemoteAlreadyPresentInEvent()
    {
        $listener = new CheckForRemoteOptionListener();
        $this->event->remote()->willReturn('origin');

        $this->assertNull($listener($this->event->reveal()));

        $this->event->input()->shouldNotHaveBeenCalled();
    }

    public function testListenerReturnsWithoutModifyingEventIfNoRemoteOptionFound()
    {
        $listener = new CheckForRemoteOptionListener();
        $this->input->getOption('remote')->willReturn(null);
        $this->event->remote()->willReturn(null);
        $this->event->input()->will([$this->input, 'reveal']);

        $this->assertNull($listener($this->event->reveal()));

        $this->event->setRemote(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerSetsEventRemoteFromInputRemoteOptionWhenFound()
    {
        $listener = new CheckForRemoteOptionListener();
        $this->input->getOption('remote')->willReturn('upstream');
        $this->event->remote()->willReturn(null);
        $this->event->input()->will([$this->input, 'reveal']);
        $this->event->setRemote('upstream')->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }
}
