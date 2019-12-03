<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\IsEntryArgumentEmptyListener;
use PHPUnit\Framework\TestCase;

class IsEntryArgumentEmptyListenerTest extends TestCase
{
    protected function setUp() : void
    {
        $this->event = $this->prophesize(AddChangelogEntryEvent::class);
        $this->event->entryIsEmpty()->will(function () {
        });
    }

    public function testDoesNothingIfEventHasEntry()
    {
        $this->event->entry()->willReturn('foo')->shouldBeCalled();

        $listener = new IsEntryArgumentEmptyListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->entryIsEmpty()->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventWhenEntryIsEmpty()
    {
        $this->event->entry()->willReturn('')->shouldBeCalled();

        $listener = new IsEntryArgumentEmptyListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->entryIsEmpty()->shouldHaveBeenCalled();
    }
}
