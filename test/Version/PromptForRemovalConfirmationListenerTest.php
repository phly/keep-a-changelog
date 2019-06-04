<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Version\PromptForRemovalConfirmationListener;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PromptForRemovalConfirmationListenerTest extends TestCase
{
    public function setUp()
    {
        $this->entry  = new ChangelogEntry();
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->helper = $this->prophesize(QuestionHelper::class);
        $this->event  = $this->prophesize(RemoveChangelogVersionEvent::class);

        $this->entry->contents = 'Changelog contents';

        $this->input->hasOption(Argument::type('string'))->willReturn(null);
        $this->input->getOption(Argument::type('string'))->willReturn(null);

        $this->output->writeln(Argument::type('string'))->willReturn(null);

        $this->event->input()->will([$this->input, 'reveal']);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->changelogEntry()->willReturn($this->entry);
        $this->event->abort()->will(function () {
        });

        $this->listener = new PromptForRemovalConfirmationListener();
        $this->listener->questionHelper = $this->helper->reveal();
    }

    public function testListenerReturnsEarlyIfForceRemovalOptionToggledOn()
    {
        $this->input->hasOption('force-removal')->willReturn(true);
        $this->input->getOption('force-removal')->willReturn(true);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->input()->shouldHaveBeenCalled();
        $this->event->changelogEntry()->shouldNotHaveBeenCalled();
        $this->event->output()->shouldNotHaveBeenCalled();
        $this->event->abort()->shouldNotHaveBeenCalled();
    }

    public function testListenerAbortsEventOnUserRequest()
    {
        $this->helper
             ->ask(
                 Argument::that([$this->input, 'reveal']),
                 Argument::that([$this->output, 'reveal']),
                 Argument::type(ConfirmationQuestion::class)
             )
             ->willReturn(false);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->input()->shouldHaveBeenCalled();
        $this->event->changelogEntry()->shouldHaveBeenCalled();
        $this->event->output()->shouldHaveBeenCalled();
        $this->event->abort()->shouldHaveBeenCalled();
    }

    public function testListenerDoesNotNotifyEventIfUserDoesNotAbort()
    {
        $this->helper
             ->ask(
                 Argument::that([$this->input, 'reveal']),
                 Argument::that([$this->output, 'reveal']),
                 Argument::type(ConfirmationQuestion::class)
             )
             ->willReturn(true);

        $this->assertNull(($this->listener)($this->event->reveal()));

        $this->event->input()->shouldHaveBeenCalled();
        $this->event->changelogEntry()->shouldHaveBeenCalled();
        $this->event->output()->shouldHaveBeenCalled();
        $this->event->abort()->shouldNotHaveBeenCalled();
    }
}
