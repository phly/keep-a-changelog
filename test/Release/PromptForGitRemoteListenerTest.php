<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\PromptForGitRemoteListener;
use Phly\KeepAChangelog\Release\PushTagEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PromptForGitRemoteListenerTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class)->reveal();
        $this->output = $this->prophesize(OutputInterface::class)->reveal();
        $this->helper = $this->prophesize(QuestionHelper::class);
        $this->event  = $this->prophesize(PushTagEvent::class);
    }

    public function testListenerDoesNothingIfRemoteAlreadyPresentInEvent()
    {
        $this->event->remote()->willReturn('upstream');

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->remotes()->shouldNotHaveBeenCalled();
        $this->event->questionHelper()->shouldNotHaveBeenCalled();
        $this->event->input()->shouldNotHaveBeenCalled();
        $this->event->output()->shouldNotHaveBeenCalled();
        $this->helper->ask(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->abortRelease()->shouldNotHaveBeenCalled();
        $this->event->setRemote(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerAsksEventToAbortReleaseIfUserChoosesToAbort()
    {
        $remotes         = ['origin', 'upstream'];
        $promptedRemotes = array_merge($remotes, ['abort' => 'Abort release']);

        $this->event->remote()->willReturn(null);
        $this->event->questionHelper()->will([$this->helper, 'reveal']);
        $this->event->remotes()->willReturn($remotes);
        $this->event->input()->willReturn($this->input);
        $this->event->output()->willReturn($this->output);
        $this->event->abortRelease()->shouldBeCalled();

        $this->helper
            ->ask(
                $this->input,
                $this->output,
                Argument::that(function ($question) use ($promptedRemotes) {
                    TestCase::assertInstanceOf(ChoiceQuestion::class, $question);
                    TestCase::assertSame($promptedRemotes, $question->getChoices());
                    return $question;
                })
            )
            ->willReturn('Abort release');

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->setRemote(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerSetsRemoteInEventBasedOnUserSelection()
    {
        $remotes         = ['origin', 'upstream'];
        $promptedRemotes = array_merge($remotes, ['abort' => 'Abort release']);

        $this->event->remote()->willReturn(null);
        $this->event->remotes()->willReturn($remotes);
        $this->event->questionHelper()->will([$this->helper, 'reveal']);
        $this->event->input()->willReturn($this->input);
        $this->event->output()->willReturn($this->output);

        $this->helper
            ->ask(
                $this->input,
                $this->output,
                Argument::that(function ($question) use ($promptedRemotes) {
                    TestCase::assertInstanceOf(ChoiceQuestion::class, $question);
                    TestCase::assertSame($promptedRemotes, $question->getChoices());
                    return $question;
                })
            )
            ->willReturn('upstream');

        $this->event->setRemote('upstream')->shouldBeCalled();

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->abortRelease()->shouldNotHaveBeenCalled();
    }
}
