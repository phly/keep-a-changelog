<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\CheckTreeForChangesListener;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckTreeForChangesListenerTest extends TestCase
{
    public function testListenerDoesNothingIfForceFlagIsPresent(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())->method('getOption')->with('force')->willReturn(true);

        $event = $this->createMock(TagReleaseEvent::class);
        $event->expects($this->once())->method('input')->willReturn($input);
        $event->expects($this->never())->method('unversionedChangesPresent');
        $event->expects($this->never())->method('output');

        $listener = new CheckTreeForChangesListener();
        $this->assertNull($listener($event));
    }

    public function testListenerDoesNothingIfForceFlagNotPresentButTreeIsClean(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())->method('getOption')->with('force')->willReturn(true);

        $event = $this->createMock(TagReleaseEvent::class);
        $event->expects($this->once())->method('input')->willReturn($input);
        $event->expects($this->never())->method('unversionedChangesPresent');
        $event->expects($this->never())->method('output');

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 0;
        };

        $this->assertNull($listener($event));
    }

    public function testListenerNotifesEventThatTaggingFailedIfForceFlagNotPresentAndTreeIsDirty(): void
    {
        $input  = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $event  = $this->createMock(TagReleaseEvent::class);

        $input->expects($this->once())->method('getOption')->with('force')->willReturn(null);
        $event->expects($this->once())->method('input')->willReturn($input);
        $event->expects($this->once())->method('unversionedChangesPresent');

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $output[] = 'some output';
            $return   = 0;
        };

        $this->assertNull($listener($event));
    }

    public function testListenerNotifesEventThatTaggingFailedIfForceFlagNotPresentAndStatusCheckFails(): void
    {
        $input  = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $event  = $this->createMock(TagReleaseEvent::class);

        $input->expects($this->once())->method('getOption')->with('force')->willReturn(null);
        $event->expects($this->once())->method('input')->willReturn($input);
        $event->expects($this->once())->method('unversionedChangesPresent');

        $listener       = new CheckTreeForChangesListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 1;
        };

        $this->assertNull($listener($event));
    }
}
