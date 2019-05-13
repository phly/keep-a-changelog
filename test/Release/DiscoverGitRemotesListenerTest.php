<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\DiscoverGitRemotesListener;
use Phly\KeepAChangelog\Release\PushTagEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DiscoverGitRemotesListenerTest extends TestCase
{
    public function setUp()
    {
        $this->event = $this->prophesize(PushTagEvent::class);
    }

    public function testReturnsWithoutDoingWorkIfRemoteIsAlreadyPresentInEvent()
    {
        $exec = function () {
            TestCase::fail('Exec should not have been called');
        };
        $this->event->remote()->willReturn('upstream');

        $listener = new DiscoverGitRemotesListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event->reveal()));

        $this->event->gitRemoteResolutionFailed()->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testMarksResolutionFailedInEventIfExitStatusIsNonZero()
    {
        $exec = function (string $command, array &$output, int &$exitStatus) {
            TestCase::assertSame('git remote -v', $command);
            $exitStatus = 1;
        };
        $this->event->remote()->willReturn(null);
        $this->event->gitRemoteResolutionFailed()->shouldBeCalled();

        $listener = new DiscoverGitRemotesListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event->reveal()));

        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testSetsRemotesFromOutputWhenExitStatusIsZero()
    {
        $exec = function (string $command, array &$output, int &$exitStatus) {
            TestCase::assertSame('git remote -v', $command);
            $output     = ['origin', 'upstream'];
            $exitStatus = 0;
        };
        $this->event->remote()->willReturn(null);
        $this->event->setRemotes(['origin', 'upstream'])->shouldBeCalled();

        $listener = new DiscoverGitRemotesListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event->reveal()));

        $this->event->gitRemoteResolutionFailed()->shouldNotHaveBeenCalled();
    }
}
