<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\PromptToSaveTokenListener;
use Phly\KeepAChangelog\Release\SaveTokenEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PromptToSaveTokenListenerTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class)->reveal();
        $this->output = $this->prophesize(OutputInterface::class)->reveal();
        $this->helper = $this->prophesize(QuestionHelper::class);
        $this->event  = $this->prophesize(SaveTokenEvent::class);
        $this->event->input()->willReturn($this->input);
        $this->event->output()->willReturn($this->output);
        $this->event->questionHelper()->will([$this->helper, 'reveal']);
    }

    public function testListenerDoesNothingIfUserChoosesToSaveToken()
    {
        $this->helper
            ->ask($this->input, $this->output, Argument::type(ConfirmationQuestion::class))
            ->willReturn(true);

        $listener = new PromptToSaveTokenListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->abort()->shouldNotHaveBeenCalled();
    }

    public function testListenerAbortsEventIfUserChoosesNotToSaveToken()
    {
        $this->helper
            ->ask($this->input, $this->output, Argument::type(ConfirmationQuestion::class))
            ->willReturn(false);
        $this->event->abort()->shouldBeCalled();

        $listener = new PromptToSaveTokenListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
