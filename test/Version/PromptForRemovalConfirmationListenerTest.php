<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Version\PromptForRemovalConfirmationListener;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PromptForRemovalConfirmationListenerTest extends TestCase
{
    private ChangelogEntry $entry;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private QuestionHelper&MockObject $helper;
    private RemoveChangelogVersionEvent&MockObject $event;
    private PromptForRemovalConfirmationListener $listener;

    protected function setUp(): void
    {
        $this->entry  = new ChangelogEntry();
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->helper = $this->createMock(QuestionHelper::class);
        $this->event  = $this->createMock(RemoveChangelogVersionEvent::class);

        $this->entry->contents = 'Changelog contents';

        $this->event->expects($this->any())->method('input')->willReturn($this->input);
        $this->event->expects($this->any())->method('output')->willReturn($this->output);
        $this->event->expects($this->any())->method('changelogEntry')->willReturn($this->entry);

        $this->listener                 = new PromptForRemovalConfirmationListener();
        $this->listener->questionHelper = $this->helper;
    }

    public function testListenerReturnsEarlyIfForceRemovalOptionToggledOn()
    {
        $this->input->expects($this->once())->method('hasOption')->with('force-removal')->willReturn(true);
        $this->input->expects($this->once())->method('getOption')->with('force-removal')->willReturn(true);

        $this->event->expects($this->atLeastOnce())->method('input');
        $this->event->expects($this->never())->method('output');
        $this->event->expects($this->never())->method('changelogEntry');
        $this->event->expects($this->never())->method('abort');

        $this->assertNull(($this->listener)($this->event));
    }

    public function testListenerAbortsEventOnUserRequest()
    {
        $this->input->expects($this->once())->method('hasOption')->with('force-removal')->willReturn(false);

        $this->event->expects($this->atLeastOnce())->method('input');
        $this->event->expects($this->atLeastOnce())->method('changelogEntry');
        $this->event->expects($this->atLeastOnce())->method('output');
        $this->event->expects($this->atLeastOnce())->method('abort');

        $this->helper
            ->expects($this->once())
            ->method('ask')
            ->with($this->input, $this->output, $this->isInstanceOf(ConfirmationQuestion::class))
            ->willReturn(false);

        $this->assertNull(($this->listener)($this->event));
    }

    public function testListenerDoesNotNotifyEventIfUserDoesNotAbort()
    {
        $this->input->expects($this->once())->method('hasOption')->with('force-removal')->willReturn(false);

        $this->event->expects($this->atLeastOnce())->method('input');
        $this->event->expects($this->atLeastOnce())->method('changelogEntry');
        $this->event->expects($this->atLeastOnce())->method('output');
        $this->event->expects($this->never())->method('abort');

        $this->helper
            ->expects($this->once())
            ->method('ask')
            ->with($this->input, $this->output, $this->isInstanceOf(ConfirmationQuestion::class))
            ->willReturn(true);

        $this->assertNull(($this->listener)($this->event));
    }
}
