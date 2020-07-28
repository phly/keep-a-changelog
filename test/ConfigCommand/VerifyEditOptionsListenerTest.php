<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
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
        $event = $this->prophesize(EditConfigEvent::class);
        $event->editLocal()->willReturn($editLocal);
        $event->editGlobal()->willReturn($editGlobal);

        $tooManyOptions = $event->tooManyOptions();
        $notifiesEvents
            ? $tooManyOptions->shouldBeCalled()
            : $tooManyOptions->shouldNotBeenCalled();

        $listener = new VerifyEditOptionsListener();

        $this->assertNull($listener($event->reveal()));
    }
}
