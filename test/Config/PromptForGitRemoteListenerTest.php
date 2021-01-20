<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config\PromptForGitRemoteListener;
use Phly\KeepAChangelog\Config\RemoteNameDiscovery;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PromptForGitRemoteListenerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->input  = $this->prophesize(InputInterface::class)->reveal();
        $this->output = $this->prophesize(OutputInterface::class)->reveal();
        $this->helper = $this->prophesize(QuestionHelper::class);
        $this->event  = $this->prophesize(RemoteNameDiscovery::class);
        $this->event->input()->willReturn($this->input);
        $this->event->output()->willReturn($this->output);
        $this->event->questionHelper()->will([$this->helper, 'reveal']);
    }

    public function testListenerReturnsEarlyIfEventIndicatesRemoteAlreadyFound()
    {
        $this->event->remoteWasFound()->willReturn(true);

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->remotes()->shouldNotHaveBeenCalled();
        $this->event->questionHelper()->shouldNotHaveBeenCalled();
        $this->event->input()->shouldNotHaveBeenCalled();
        $this->event->output()->shouldNotHaveBeenCalled();
        $this->event->abort()->shouldNotHaveBeenCalled();
        $this->event->foundRemote()->shouldNotHaveBeenCalled();
    }

    public function testListenerAbortsEventOnUserRequest()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->event->remotes()->willReturn(['origin']);
        $this->event->abort()->shouldBeCalled();
        $this->helper
            ->ask(
                $this->input,
                $this->output,
                Argument::type(ChoiceQuestion::class)
            )
            ->willReturn('abort');

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->remotes()->shouldHaveBeenCalled();
        $this->event->questionHelper()->shouldHaveBeenCalled();
        $this->event->input()->shouldHaveBeenCalled();
        $this->event->output()->shouldHaveBeenCalled();
        $this->event->foundRemote()->shouldNotHaveBeenCalled();
    }

    public function testListenerNotifiesEventOfChosenRemote()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->event->remotes()->willReturn(['origin']);
        $this->event->foundRemote('origin')->shouldBeCalled();
        $this->helper
            ->ask(
                $this->input,
                $this->output,
                Argument::type(ChoiceQuestion::class)
            )
            ->willReturn(0);

        $listener = new PromptForGitRemoteListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->remotes()->shouldHaveBeenCalled();
        $this->event->questionHelper()->shouldHaveBeenCalled();
        $this->event->input()->shouldHaveBeenCalled();
        $this->event->output()->shouldHaveBeenCalled();
        $this->event->abort()->shouldNotHaveBeenCalled();
    }
}
