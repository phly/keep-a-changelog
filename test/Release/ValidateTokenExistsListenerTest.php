<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Release\ValidateRequirementsEvent;
use Phly\KeepAChangelog\Release\ValidateTokenExistsListener;
use PHPUnit\Framework\TestCase;

class ValidateTokenExistsListenerTest extends TestCase
{
    public function testListenerNotifiesEventThatTokenIsNotFoundIfNoConfigPresent()
    {
        $event = $this->prophesize(ValidateRequirementsEvent::class);
        $event->config()->willReturn(null);
        $event->tokenNotFound()->shouldBeCalled();

        $listener = new ValidateTokenExistsListener();

        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerNotifiesEventThatTokenIsNotFoundIfConfigHasNoToken()
    {
        $config = $this->prophesize(Config::class);
        $config->token()->willReturn('');

        $event = $this->prophesize(ValidateRequirementsEvent::class);
        $event->config()->will([$config, 'reveal']);
        $event->tokenNotFound()->shouldBeCalled();

        $listener = new ValidateTokenExistsListener();

        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerDoesNothingWithEventWhenConfigPresentWithToken()
    {
        $config = $this->prophesize(Config::class);
        $config->token()->willReturn('some-token');

        $event = $this->prophesize(ValidateRequirementsEvent::class);
        $event->config()->will([$config, 'reveal']);

        $listener = new ValidateTokenExistsListener();

        $this->assertNull($listener($event->reveal()));
        $event->tokenNotFound()->shouldNotHaveBeenCalled();
    }
}
