<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\CheckTreeForChangesListener;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckTreeForChangesListenerTest extends TestCase
{
    public function testListenerDoesNothingIfForceFlagIsPresent(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption('force')->willReturn(true)->shouldBeCalled();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->input()->will([$input, 'reveal'])->shouldBeCalled();
        $event->taggingFailed()->shouldNotBeCalled();
        $event->output()->shouldNotBeCalled();

        $listener = new CheckTreeForChangesListener();
        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerDoesNothingIfForceFlagNotPresentButTreeIsClean(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption('force')->willReturn(null)->shouldBeCalled();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->input()->will([$input, 'reveal'])->shouldBeCalled();
        $event->taggingFailed()->shouldNotBeCalled();
        $event->output()->shouldNotBeCalled();

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 0;
        };

        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerNotifesEventThatTaggingFailedIfForceFlagNotPresentAndTreeIsDirty(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption('force')->willReturn(null)->shouldBeCalled();

        $output = $this->prophesize(OutputInterface::class);
        $output->write(Argument::containingString('not checked in'))->shouldBeCalled();
        $output->write(Argument::containingString('use the --force'))->shouldBeCalled();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->input()->will([$input, 'reveal'])->shouldBeCalled();
        $event->taggingFailed()->shouldBeCalled();
        $event->output()->will([$output, 'reveal'])->shouldBeCalled();

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $output[] = 'some output';
            $return   = 0;
        };

        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerNotifesEventThatTaggingFailedIfForceFlagNotPresentAndStatusCheckFails(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption('force')->willReturn(null)->shouldBeCalled();

        $output = $this->prophesize(OutputInterface::class);
        $output->write(Argument::containingString('not checked in'))->shouldBeCalled();
        $output->write(Argument::containingString('use the --force'))->shouldBeCalled();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->input()->will([$input, 'reveal'])->shouldBeCalled();
        $event->taggingFailed()->shouldBeCalled();
        $event->output()->will([$output, 'reveal'])->shouldBeCalled();

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 1;
        };

        $this->assertNull($listener($event->reveal()));
    }
}
