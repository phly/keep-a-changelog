<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config\PromptForGitRemoteListener;
use Phly\KeepAChangelog\Config\RemoteNameDiscovery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PromptForGitRemoteListenerTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private QuestionHelper&MockObject $helper;
    private RemoteNameDiscovery&MockObject $event;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->helper = $this->createMock(QuestionHelper::class);
        $this->event  = $this->createMock(RemoteNameDiscovery::class);

        $this->event->expects($this->any())->method('input')->willReturn($this->input);
        $this->event->expects($this->any())->method('output')->willReturn($this->output);
        $this->event->expects($this->any())->method('questionHelper')->willReturn($this->helper);
    }

    public function testListenerReturnsEarlyIfEventIndicatesRemoteAlreadyFound()
    {
        $this->event->expects($this->never())->method('abort');
        $this->event->expects($this->never())->method('foundRemote');
        $this->event->expects($this->never())->method('input');
        $this->event->expects($this->never())->method('output');
        $this->event->expects($this->never())->method('questionHelper');
        $this->event->expects($this->never())->method('remotes');
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(true);

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event));
    }

    public function testListenerAbortsEventOnUserRequest()
    {
        $this->event->expects($this->once())->method('abort');
        $this->event->expects($this->never())->method('foundRemote');
        $this->event->expects($this->once())->method('input')->willReturn($this->input);
        $this->event->expects($this->once())->method('output')->willReturn($this->output);
        $this->event->expects($this->once())->method('questionHelper')->willReturn($this->helper);
        $this->event->expects($this->once())->method('remotes')->willReturn(['origin']);
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);

        $this->helper
            ->expects($this->once())
            ->method('ask')
            ->with($this->input, $this->output, $this->isInstanceOf(ChoiceQuestion::class))
            ->willReturn('abort');

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event));
    }

    public function testListenerNotifiesEventOfChosenRemote()
    {
        $this->event->expects($this->never())->method('abort');
        $this->event->expects($this->once())->method('foundRemote')->with('origin');
        $this->event->expects($this->once())->method('input')->willReturn($this->input);
        $this->event->expects($this->once())->method('output')->willReturn($this->output);
        $this->event->expects($this->once())->method('questionHelper')->willReturn($this->helper);
        $this->event->expects($this->once())->method('remotes')->willReturn(['origin']);
        $this->event->expects($this->once())->method('remoteWasFound')->willReturn(false);

        $this->helper
            ->expects($this->once())
            ->method('ask')
            ->with($this->input, $this->output, $this->isInstanceOf(ChoiceQuestion::class))
            ->willReturn(0);

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event));
    }
}
