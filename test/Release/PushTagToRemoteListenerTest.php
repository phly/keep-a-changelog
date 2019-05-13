<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\PushTagEvent;
use Phly\KeepAChangelog\Release\PushTagToRemoteListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class PushTagToRemoteListenerTest extends TestCase
{
    public function setUp()
    {
        $this->output = $this->prophesize(OutputInterface::class);
        $this->event  = $this->prophesize(PushTagEvent::class);
    }

    public function testNotifesEventPushSucceeded()
    {
        $tagName = 'v1.2.3';
        $remote  = 'upstream';

        $this->event->tagName()->willReturn($tagName);
        $this->event->remote()->willReturn($remote);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->pushSucceeded()->shouldBeCalled();

        $exec    = function (string $command, array &$output, int &$exitStatus) use ($tagName, $remote) {
            TestCase::assertSame(sprintf('git push %s %s', $remote, $tagName), $command);
            $exitStatus = 0;
        };
        $listener = new PushTagToRemoteListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event->reveal()));

        $this->output
            ->writeln(Argument::containingString('Pushing tag v1.2.3 to upstream'))
            ->shouldHaveBeenCalled();
    }

    public function testNotifesEventPushFailed()
    {
        $tagName = 'v1.2.3';
        $remote  = 'upstream';

        $this->event->tagName()->willReturn($tagName);
        $this->event->remote()->willReturn($remote);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->pushFailed()->shouldBeCalled();

        $exec    = function (string $command, array &$output, int &$exitStatus) use ($tagName, $remote) {
            TestCase::assertSame(sprintf('git push %s %s', $remote, $tagName), $command);
            $exitStatus = 1;
        };
        $listener = new PushTagToRemoteListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event->reveal()));

        $this->output
            ->writeln(Argument::containingString('Pushing tag v1.2.3 to upstream'))
            ->shouldHaveBeenCalled();
    }
}
