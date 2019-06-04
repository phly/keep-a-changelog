<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReleaseEvent;
use Phly\KeepAChangelog\Version\VerifyTagExistsListener;
use PHPUnit\Framework\TestCase;

class VerifyTagExistsListenerTest extends TestCase
{
    public function setUp()
    {
        $this->event = $this->prophesize(ReleaseEvent::class);
        $this->event
            ->tagName()
            ->willReturn('v1.2.3');
    }

    public function testCallsExecAndDoesNothingWhenReturnIsZero()
    {
        $listener       = new VerifyTagExistsListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 0;
        };

        $this->assertNull($listener($this->event->reveal()));

        $this->event->couldNotFindTag()->shouldNotHaveBeenCalled();
    }

    public function testCallsExecAndIndicatesTagNotFoundWhenReturnIsNotZero()
    {
        $listener       = new VerifyTagExistsListener();
        $listener->exec = function ($command, &$output, &$return) {
            $return = 1;
        };

        $this->event->couldNotFindTag()->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }
}
