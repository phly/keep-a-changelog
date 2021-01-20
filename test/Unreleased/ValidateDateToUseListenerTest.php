<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use Phly\KeepAChangelog\Unreleased\ValidateDateToUseListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ValidateDateToUseListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testDoesNothingIfReleaseDateIsValid(): void
    {
        $event = $this->prophesize(PromoteEvent::class);
        $event->releaseDate()->willReturn('2020-07-16')->shouldBeCalled();

        $listener = new ValidateDateToUseListener();

        $this->assertNull($listener($event->reveal()));
        $event->didNotPromote()->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventOfInabilityToPromote(): void
    {
        $event = $this->prophesize(PromoteEvent::class);
        $event->releaseDate()->willReturn('TBD')->shouldBeCalled();
        $event->didNotPromote()->shouldBeCalled();

        $listener = new ValidateDateToUseListener();

        $this->assertNull($listener($event->reveal()));
    }
}
