<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Common\EventInterface;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\ConfigDiscovery;
use Phly\KeepAChangelog\Config\ConfigListener;
use Phly\KeepAChangelog\Config\PackageNameDiscovery;
use Phly\KeepAChangelog\Config\RemoteNameDiscovery;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigListenerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->config    = new Config();
        $this->discovery = $this->prophesize(ConfigDiscovery::class);
        $this->discovery->config()->willReturn($this->config);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->input      = $this->prophesize(InputInterface::class)->reveal();
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->event      = $this->prophesize(EventInterface::class);
        $this->event->input()->willReturn($this->input);
        $this->event->output()->will([$this->output, 'reveal']);
        $this->event->dispatcher()->will([$this->dispatcher, 'reveal']);
    }

    public function createListener(
        bool $requiresPackageName = false,
        bool $requiresRemoteName = false
    ): ConfigListener {
        return new ConfigListener(
            $requiresPackageName,
            $requiresRemoteName
        );
    }

    public function testTriggersConfigDiscoveryAndMarksConfigurationDiscoveredWhenNeitherPackageNorRemoteRequired()
    {
        $this->dispatcher
            ->dispatch(Argument::type(ConfigDiscovery::class))
            ->will([$this->discovery, 'reveal']);

        $this->event
            ->discoveredConfiguration($this->config)
            ->shouldBeCalled();

        $listener = $this->createListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->dispatcher->dispatch(Argument::type(PackageNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->dispatcher->dispatch(Argument::type(RemoteNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->output->writeln(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNoPackageCheckPerformedIfPackageAlreadyExistsInConfig()
    {
        $this->config->setPackage('some/package');

        $this->dispatcher
            ->dispatch(Argument::type(ConfigDiscovery::class))
            ->will([$this->discovery, 'reveal']);

        $this->event
            ->discoveredConfiguration($this->config)
            ->shouldBeCalled();

        $listener = $this->createListener($requiresPackageName = true);

        $this->assertNull($listener($this->event->reveal()));
        $this->dispatcher->dispatch(Argument::type(PackageNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->dispatcher->dispatch(Argument::type(RemoteNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->output->writeln(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNoRemoteCheckPerformedIfPackageAlreadyExistsInConfig()
    {
        $this->config->setRemote('upstream');

        $this->dispatcher
            ->dispatch(Argument::type(ConfigDiscovery::class))
            ->will([$this->discovery, 'reveal']);

        $this->event
            ->discoveredConfiguration($this->config)
            ->shouldBeCalled();

        $listener = $this->createListener($requiresPackageName = false, $requiresRemoteName = true);

        $this->assertNull($listener($this->event->reveal()));
        $this->dispatcher->dispatch(Argument::type(PackageNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->dispatcher->dispatch(Argument::type(RemoteNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->output->writeln(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testMarksConfigurationIncompleteIfPackageNameDiscoveryFails()
    {
        $this->dispatcher
            ->dispatch(Argument::type(ConfigDiscovery::class))
            ->will([$this->discovery, 'reveal']);

        $this->dispatcher
            ->dispatch(Argument::type(PackageNameDiscovery::class))
            ->willReturn(new PackageNameDiscovery(
                $this->input,
                $this->output->reveal(),
                $this->config
            ));

        $this->output->writeln(Argument::containingString('Unable to determine package name'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('do one of the following'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('Add a "package" setting'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('--package option'))->shouldBeCalled();

        $this->event->configurationIncomplete()->shouldBeCalled();

        $listener = $this->createListener($requiresPackageName = true);

        $this->assertNull($listener($this->event->reveal()));
        $this->dispatcher->dispatch(Argument::type(RemoteNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->event->discoveredConfiguration(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testMarksConfigurationIncompleteIfRemoteNameDiscoveryFails()
    {
        $this->dispatcher
            ->dispatch(Argument::type(ConfigDiscovery::class))
            ->will([$this->discovery, 'reveal']);

        $this->dispatcher
            ->dispatch(Argument::type(RemoteNameDiscovery::class))
            ->willReturn(new RemoteNameDiscovery(
                $this->input,
                $this->output->reveal(),
                $this->config,
                new QuestionHelper()
            ));

        $this->output->writeln(Argument::containingString('Unable to determine Git remote'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('do one of the following'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('Add a "remote" setting'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('--remote option'))->shouldBeCalled();

        $this->event->configurationIncomplete()->shouldBeCalled();

        $listener = $this->createListener($requiresPackageName = false, $requiresRemoteName = true);

        $this->assertNull($listener($this->event->reveal()));
        $this->dispatcher->dispatch(Argument::type(PackageNameDiscovery::class))->shouldNotHaveBeenCalled();
        $this->event->discoveredConfiguration(Argument::any())->shouldNotHaveBeenCalled();
    }
}
