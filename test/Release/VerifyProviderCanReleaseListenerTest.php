<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Release\ReleaseEvent;
use Phly\KeepAChangelog\Release\VerifyProviderCanReleaseListener;
use PHPUnit\Framework\TestCase;

class VerifyProviderCanReleaseListenerTest extends TestCase
{
    public function testListenerNotifiesEventThatProviderIsIncompleteIfProviderCannotRelease()
    {
        $provider = $this->prophesize(ProviderInterface::class);
        $provider->canCreateRelease()->willReturn(false);
        $config = new Config();
        $config->setProvider($provider->reveal());

        $event = $this->prophesize(ReleaseEvent::class);
        $event->config()->willReturn($config);
        $event->providerIsIncomplete()->shouldBeCalled();

        $listener = new VerifyProviderCanReleaseListener();

        $this->assertNull($listener($event->reveal()));
    }

    public function testListenerDoesNothingWithEventWhenProviderCanRelease()
    {
        $provider = $this->prophesize(ProviderInterface::class);
        $provider->canCreateRelease()->willReturn(true);
        $config = new Config();
        $config->setProvider($provider->reveal());

        $event = $this->prophesize(ReleaseEvent::class);
        $event->config()->willReturn($config);

        $listener = new VerifyProviderCanReleaseListener();

        $this->assertNull($listener($event->reveal()));
        $event->providerIsIncomplete()->shouldNotHaveBeenCalled();
    }
}
