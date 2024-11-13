<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\RemoveConfigEvent;
use Phly\KeepAChangelog\ConfigCommand\VerifyRemoveOptionsListener;
use PHPUnit\Framework\TestCase;

class VerifyRemoveOptionsListenerTest extends TestCase
{
    public function eventOptions(): iterable
    {
        yield 'neither true' => [$removeLocal = false, $removeGlobal = false, $notifiesEvent = true];
        yield 'local true'   => [$removeLocal = true, $removeGlobal = false, $notifiesEvent = false];
        yield 'global true'  => [$removeLocal = false, $removeGlobal = true, $notifiesEvent = false];
        yield 'both true'    => [$removeLocal = true, $removeGlobal = true, $notifiesEvent = false];
    }

    /**
     * @dataProvider eventOptions
     */
    public function testNotifiesEventUnderCorrectCircumstances(
        bool $removeLocal,
        bool $removeGlobal,
        bool $notifiesEvents
    ) {
        $event = $this->createMock(RemoveConfigEvent::class);
        $event->expects($this->any())->method('removeLocal')->willReturn($removeLocal);
        $event->expects($this->any())->method('removeGlobal')->willReturn($removeGlobal);

        $notifiesEvents
            ? $event->expects($this->once())->method('missingOptions')
            : $event->expects($this->never())->method('missingOptions');

        $listener = new VerifyRemoveOptionsListener();

        $this->assertNull($listener($event));
    }
}
