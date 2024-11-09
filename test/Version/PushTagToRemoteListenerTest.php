<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\PushTagToRemoteListener;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class PushTagToRemoteListenerTest extends TestCase
{
    private Config $config;
    private OutputInterface&MockObject $output;
    private ReleaseEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->output = $this->createMock(OutputInterface::class);
        $this->event  = $this->createMock(ReleaseEvent::class);

        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testDoesNothingWithEventIfPushSucceeded()
    {
        $tagName = 'v1.2.3';
        $remote  = 'upstream';

        $this->config->setRemote($remote);
        $this->event->expects($this->atLeastOnce())->method('tagName')->willReturn($tagName);
        $this->event->expects($this->atLeastOnce())->method('output')->willReturn($this->output);
        $this->event->expects($this->never())->method('taggingFailed');

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Pushing tag v1.2.3 to upstream'));

        $exec           = function (string $command, array &$output, int &$exitStatus) use ($tagName, $remote) {
            TestCase::assertSame(sprintf('git push %s %s', $remote, $tagName), $command);
            $exitStatus = 0;
        };
        $listener       = new PushTagToRemoteListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event));
    }

    public function testNotifesEventPushFailed()
    {
        $tagName = 'v1.2.3';
        $remote  = 'upstream';

        $this->config->setRemote($remote);
        $this->event->expects($this->atLeastOnce())->method('tagName')->willReturn($tagName);
        $this->event->expects($this->atLeastOnce())->method('output')->willReturn($this->output);
        $this->event->expects($this->atLeastOnce())->method('taggingFailed');

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Pushing tag v1.2.3 to upstream'));

        $exec           = function (string $command, array &$output, int &$exitStatus) use ($tagName, $remote) {
            TestCase::assertSame(sprintf('git push %s %s', $remote, $tagName), $command);
            $exitStatus = 1;
        };
        $listener       = new PushTagToRemoteListener();
        $listener->exec = $exec;

        $this->assertNull($listener($this->event));
    }
}
