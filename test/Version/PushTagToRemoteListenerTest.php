<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\PushTagToRemoteListener;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class PushTagToRemoteListenerTest extends TestCase
{
    protected function setUp() : void
    {
        $this->config = new Config();
        $this->output = $this->prophesize(OutputInterface::class);
        $this->event  = $this->prophesize(ReleaseEvent::class);
        $this->event->config()->willReturn($this->config);
    }

    public function testDoesNothingWithEventIfPushSucceeded()
    {
        $tagName = 'v1.2.3';
        $remote  = 'upstream';

        $this->event->tagName()->willReturn($tagName);
        $this->config->setRemote($remote);
        $this->event->output()->will([$this->output, 'reveal']);

        $exec           = function (string $command, array &$output, int &$exitStatus) use ($tagName, $remote) {
            TestCase::assertSame(sprintf('git push %s %s', $remote, $tagName), $command);
            $exitStatus = 0;
        };
        $listener       = new PushTagToRemoteListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event->reveal()));

        $this->output
            ->writeln(Argument::containingString('Pushing tag v1.2.3 to upstream'))
            ->shouldHaveBeenCalled();
        $this->event->taggingFailed()->shouldNotHaveBeenCalled();
    }

    public function testNotifesEventPushFailed()
    {
        $tagName = 'v1.2.3';
        $remote  = 'upstream';

        $this->event->tagName()->willReturn($tagName);
        $this->config->setRemote($remote);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->taggingFailed()->shouldBeCalled();

        $exec           = function (string $command, array &$output, int &$exitStatus) use ($tagName, $remote) {
            TestCase::assertSame(sprintf('git push %s %s', $remote, $tagName), $command);
            $exitStatus = 1;
        };
        $listener       = new PushTagToRemoteListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event->reveal()));

        $this->output
            ->writeln(Argument::containingString('Pushing tag v1.2.3 to upstream'))
            ->shouldHaveBeenCalled();
    }
}
