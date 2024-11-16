<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Version\PushReleaseToProviderListener;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class PushReleaseToProviderListenerTest extends TestCase
{
    private OutputInterface&MockObject $output;
    private ReleaseEvent&MockObject $event;
    private ProviderInterface&MockObject $provider;

    protected function setUp(): void
    {
        $this->output   = $this->createMock(OutputInterface::class);
        $this->event    = $this->createMock(ReleaseEvent::class);
        $this->provider = $this->createMock(ProviderInterface::class);

        $this->event->expects($this->any())->method('releaseName')->willReturn('some/package 1.2.3');
        $this->event->expects($this->any())->method('provider')->willReturn($this->provider);
        $this->event->expects($this->any())->method('output')->willReturn($this->output);
        $this->event->expects($this->any())->method('version')->willReturn('1.2.3');
        $this->event->expects($this->any())->method('changelog')->willReturn('this is the changelog');

        $this->output
            ->expects($this->atLeastOnce())
            ->method('writeln')
            ->with($this->stringContains('Creating release "some/package 1.2.3"'));
    }

    public function testMarksReleaseErrorWhenProviderRaisesException()
    {
        $e = new RuntimeException();

        $this->provider
            ->expects($this->once())
            ->method('createRelease')
            ->with('some/package 1.2.3', '1.2.3', 'this is the changelog')
            ->willThrowException($e);
        $this->event->expects($this->once())->method('errorCreatingRelease')->with($e);
        $this->event->expects($this->once())->method('tagName')->willReturn('1.2.3');
        $this->event->expects($this->never())->method('unexpectedProviderResult');
        $this->event->expects($this->never())->method('releaseCreated');

        $listener = new PushReleaseToProviderListener();

        $this->assertNull($listener($this->event));
    }

    public function testReportsProviderProblemIfProviderDoesNotReturnValueAfterCreatingRelease()
    {
        $this->provider
            ->expects($this->once())
            ->method('createRelease')
            ->with('some/package 1.2.3', '1.2.3', 'this is the changelog')
            ->willReturn(null);
        $this->event->expects($this->never())->method('errorCreatingRelease');
        $this->event->expects($this->once())->method('tagName')->willReturn('1.2.3');
        $this->event->expects($this->once())->method('unexpectedProviderResult');
        $this->event->expects($this->never())->method('releaseCreated');

        $listener = new PushReleaseToProviderListener();

        $this->assertNull($listener($this->event));
    }

    public function testMarksReleaseCreatedOnSuccess()
    {
        $this->provider
            ->expects($this->once())
            ->method('createRelease')
            ->with('some/package 1.2.3', '1.2.3', 'this is the changelog')
            ->willReturn('url-to-release');
        $this->event->expects($this->never())->method('errorCreatingRelease');
        $this->event->expects($this->once())->method('tagName')->willReturn('1.2.3');
        $this->event->expects($this->never())->method('unexpectedProviderResult');
        $this->event->expects($this->once())->method('releaseCreated')->with('url-to-release');

        $listener = new PushReleaseToProviderListener();

        $this->assertNull($listener($this->event));
    }
}
