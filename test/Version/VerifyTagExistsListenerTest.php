<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReleaseEvent;
use Phly\KeepAChangelog\Version\VerifyTagExistsListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VerifyTagExistsListenerTest extends TestCase
{
    private ReleaseEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->event = $this->createMock(ReleaseEvent::class);
        $this->event->expects($this->any())->method('tagName')->willReturn('v1.2.3');
    }

    public function testCallsExecAndDoesNothingWhenReturnIsZero()
    {
        $listener       = new VerifyTagExistsListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 0;
        };

        $this->event->expects($this->never())->method('couldNotFindTag');

        $this->assertNull($listener($this->event));
    }

    public function testCallsExecAndIndicatesTagNotFoundWhenReturnIsNotZero()
    {
        $listener       = new VerifyTagExistsListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 1;
        };

        $this->event->expects($this->once())->method('couldNotFindTag');

        $this->assertNull($listener($this->event));
    }
}
