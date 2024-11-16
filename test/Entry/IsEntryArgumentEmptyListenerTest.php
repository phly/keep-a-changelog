<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Entry\IsEntryArgumentEmptyListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IsEntryArgumentEmptyListenerTest extends TestCase
{
    private AddChangelogEntryEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->event = $this->createMock(AddChangelogEntryEvent::class);
        $this->event->expects($this->any())->method('entryIsEmpty');
    }

    public function testDoesNothingIfEventHasEntry()
    {
        $this->event->expects($this->atLeastOnce())->method('entry')->willReturn('foo');
        $this->event->expects($this->never())->method('entryIsEmpty');

        $listener = new IsEntryArgumentEmptyListener();

        $this->assertNull($listener($this->event));
    }

    public function testNotifiesEventWhenEntryIsEmpty()
    {
        $this->event->expects($this->atLeastOnce())->method('entry')->willReturn('');
        $this->event->expects($this->once())->method('entryIsEmpty');

        $listener = new IsEntryArgumentEmptyListener();

        $this->assertNull($listener($this->event));
}
