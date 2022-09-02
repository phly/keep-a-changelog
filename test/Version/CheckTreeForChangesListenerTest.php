<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\CheckTreeForChangesListener;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckTreeForChangesListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testListenerDoesNothingIfForceFlagIsPresent(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption('force')->willReturn(true)->shouldBeCalled();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->input()->will([$input, 'reveal'])->shouldBeCalled();
        $event->unversionedChangesPresent()->shouldNotBeCalled();
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
        $event->unversionedChangesPresent()->shouldNotBeCalled();
        $event->output()->shouldNotBeCalled();

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 0;
        };

        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerNotifesEventThatTaggingFailedIfForceFlagNotPresentAndTreeIsDirty(): void
    {
        $input  = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);
        $event  = $this->prophesize(TagReleaseEvent::class);

        $input->getOption('force')->willReturn(null)->shouldBeCalled();
        $event->input()->will([$input, 'reveal'])->shouldBeCalled();
        $event->unversionedChangesPresent()->shouldBeCalled();

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $output[] = 'some output';
            $return   = 0;
        };

        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerNotifesEventThatTaggingFailedIfForceFlagNotPresentAndStatusCheckFails(): void
    {
        $input  = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);
        $event  = $this->prophesize(TagReleaseEvent::class);

        $input->getOption('force')->willReturn(null)->shouldBeCalled();
        $event->input()->will([$input, 'reveal'])->shouldBeCalled();
        $event->unversionedChangesPresent()->shouldBeCalled();

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 1;
        };

        $this->assertNull($listener($event->reveal()));
    }
}
