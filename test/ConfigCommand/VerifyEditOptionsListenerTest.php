<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use Phly\KeepAChangelog\ConfigCommand\VerifyEditOptionsListener;
use PHPUnit\Framework\TestCase;

class VerifyEditOptionsListenerTest extends TestCase
{
    public function eventOptions(): iterable
    {
        yield 'neither true' => [$editLocal = false, $editGlobal = false, $notifiesEvent = false];
        yield 'local true'   => [$editLocal = true, $editGlobal = false, $notifiesEvent = false];
        yield 'global true'  => [$editLocal = false, $editGlobal = true, $notifiesEvent = false];
        yield 'both true'    => [$editLocal = true, $editGlobal = true, $notifiesEvent = true];
    }

    /**
     * @dataProvider eventOptions
     */
    public function testNotifiesEventUnderCorrectCircumstances(
        bool $editLocal,
        bool $editGlobal,
        bool $notifiesEvents
    ) {
        $event = $this->createMock(EditConfigEvent::class);
        $event->expects($this->any())->method('editLocal')->willReturn($editLocal);
        $event->expects($this->any())->method('editGlobal')->willReturn($editGlobal);

        $notifiesEvents
            ? $event->expects($this->once())->method('tooManyOptions')
            : $event->expects($this->never())->method('tooManyOptions');

        $listener = new VerifyEditOptionsListener();

        $this->assertNull($listener($event));
    }
}
