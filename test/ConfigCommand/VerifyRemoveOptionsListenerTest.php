<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\RemoveConfigEvent;
use Phly\KeepAChangelog\ConfigCommand\VerifyRemoveOptionsListener;
use PHPUnit\Framework\TestCase;

class VerifyRemoveOptionsListenerTest extends TestCase
{
    public function eventOptions() : iterable
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
        $event = $this->prophesize(RemoveConfigEvent::class);
        $event->removeLocal()->willReturn($removeLocal);
        $event->removeGlobal()->willReturn($removeGlobal);

        $missingOptions = $event->missingOptions();
        $notifiesEvents
            ? $missingOptions->shouldBeCalled()
            : $missingOptions->shouldNotBeenCalled();

        $listener = new VerifyRemoveOptionsListener();

        $this->assertNull($listener($event->reveal()));
    }
}
