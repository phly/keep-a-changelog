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
use Phly\KeepAChangelog\Provider\ProviderNameProviderInterface;
use Phly\KeepAChangelog\Release\MatchRemotesToPackageAndProviderListener;
use Phly\KeepAChangelog\Release\PushTagEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;

class MatchRemotesToPackageAndProviderListenerTest extends TestCase
{
    public function setUp()
    {
        $this->config   = $this->prophesize(Config::class);
        $this->event    = $this->prophesize(PushTagEvent::class);
        $this->input    = $this->prophesize(InputInterface::class);
        $this->provider = $this->prophesize(ProviderInterface::class);
    }

    public function testListenerDoesNothingIfRemoteAlreadyPresentInEvent()
    {
        $this->event->remote()->willReturn('upstream');
        $listener = new MatchRemotesToPackageAndProviderListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldNotHaveBeenCalled();
        $this->event->input()->shouldNotHaveBeenCalled();
        $this->input->getArgument(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->invalidProviderDetected(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->remotes()->shouldNotHaveBeenCalled();
        $this->event->reportNoMatchingGitRemoteFound(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * Incomplete
     *
     * I made the assumption that the Config instance stored an actual provider
     * instance, but it only stores the provider _name_. As such, I cannot test
     * further until refactoring the Config instance to accept/store the
     * provider instance itself.
     *
     * @todo
     */
    public function testListenerIndicatesInvalidProviderDetectedIfItDoesNotProvideName()
    {
        $this->markTestIncomplete();

        $provider = $this->provider->reveal();
        $this->config->provider()->willReturn($provider);
        $this->input->getArgument('package')->willReturn('some/package');
        $this->event->remote()->willReturn(null);
        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->input()->will([$this->input, 'reveal']);
        $this->event
            ->invalidProviderDetected(Argument::that(function ($type) use ($provider) {
                TestCase::assertSame(gettype($provider), $type);
                return $type;
            }))
            ->shouldBeCalled();

        $listener = new MatchRemotesToPackageAndProviderListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->remotes()->shouldNotHaveBeenCalled();
        $this->event->reportNoMatchingGitRemoteFound(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReportsNoRemoteFoundWhenNoRemotesMatchProviderAndPackage()
    {
        $this->markTestIncomplete();
    }

    public function testPushesRemoteToEventWheExactlyOneRemoteMatchesProviderAndPackage()
    {
        $this->markTestIncomplete();
    }

    public function testSetsSubsetOfRemotesWhenMultipleRemotesMatchProviderAndPackage()
    {
        $this->markTestIncomplete();
    }
}
