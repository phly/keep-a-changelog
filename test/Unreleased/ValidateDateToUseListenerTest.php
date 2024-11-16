<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use Phly\KeepAChangelog\Unreleased\ValidateDateToUseListener;
use PHPUnit\Framework\TestCase;

class ValidateDateToUseListenerTest extends TestCase
{
    public function testDoesNothingIfReleaseDateIsValid(): void
    {
        $event = $this->createMock(PromoteEvent::class);
        $event->expects($this->atLeastOnce())->method('releaseDate')->willReturn('2020-07-16');
        $event->expects($this->never())->method('didNotPromote');

        $listener = new ValidateDateToUseListener();

        $this->assertNull($listener($event));
    }

    public function testNotifiesEventOfInabilityToPromote(): void
    {
        $event = $this->createMock(PromoteEvent::class);
        $event->expects($this->atLeastOnce())->method('releaseDate')->willReturn('TBD');
        $event->expects($this->once())->method('didNotPromote');

        $listener = new ValidateDateToUseListener();

        $this->assertNull($listener($event));
    }
}
