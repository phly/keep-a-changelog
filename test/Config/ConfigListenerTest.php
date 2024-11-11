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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigListenerTest extends TestCase
{
    private Config $config;
    private ConfigDiscovery&MockObject $discovery;
    private EventDispatcherInterface&MockObject $dispatcher;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventInterface&MockObject $event;

    protected function setUp(): void
    {
        $this->config     = new Config();
        $this->discovery  = $this->createMock(ConfigDiscovery::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->event      = $this->createMock(EventInterface::class);

        $this->discovery->expects($this->any())->method('config')->willReturn($this->config);
        $this->event->expects($this->any())->method('input')->willReturn($this->input);
        $this->event->expects($this->any())->method('output')->willReturn($this->output);
        $this->event->expects($this->any())->method('dispatcher')->willReturn($this->dispatcher);
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
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ConfigDiscovery::class))
            ->willReturn($this->discovery);
        $this->output->expects($this->never())->method('writeln');
        $this->event->expects($this->once())->method('discoveredConfiguration')->with($this->config);

        $listener = $this->createListener();

        $this->assertNull($listener($this->event));
    }

    public function testNoPackageCheckPerformedIfPackageAlreadyExistsInConfig()
    {
        $this->config->setPackage('some/package');
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ConfigDiscovery::class))
            ->willReturn($this->discovery);
        $this->output->expects($this->never())->method('writeln');
        $this->event->expects($this->once())->method('discoveredConfiguration')->with($this->config);

        $listener = $this->createListener($requiresPackageName = true);

        $this->assertNull($listener($this->event));
    }

    public function testNoRemoteCheckPerformedIfPackageAlreadyExistsInConfig()
    {
        $this->config->setRemote('upstream');
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ConfigDiscovery::class))
            ->willReturn($this->discovery);
        $this->output->expects($this->never())->method('writeln');
        $this->event->expects($this->once())->method('discoveredConfiguration')->with($this->config);

        $listener = $this->createListener($requiresPackageName = false, $requiresRemoteName = true);

        $this->assertNull($listener($this->event));
    }

    public function testMarksConfigurationIncompleteIfPackageNameDiscoveryFails()
    {
        $invokedCounter = $this->exactly(2);
        $this->dispatcher
            ->expects($invokedCounter)
            ->method('dispatch')
            ->will($this->returnCallback(function ($event) use ($invokedCounter) {
                $invocationCount = $invokedCounter->getInvocationCount();
                if (1 === $invocationCount) {
                    TestCase::assertInstanceOf(ConfigDiscovery::class, $event);
                    return $this->discovery;
                }
                if (2 === $invocationCount) {
                    TestCase::assertInstanceOf(PackageNameDiscovery::class, $event);
                    return new PackageNameDiscovery($this->input, $this->output, $this->config);
                }
                return null;
            }));
        $this->event->expects($this->never())->method('discoveredConfiguration');
        $this->event->expects($this->once())->method('configurationIncomplete');

        $expectedStrings = [
            'Unable to determine package name' => false,
            'do one of the following'          => false,
            'Add a "package" setting'          => false,
            '--package option'                 => false,
        ];
        $this->output
            ->expects($this->atLeast(4))
            ->method('writeln')
            ->with($this->callback(function (string $string) use (&$expectedStrings): bool {
                foreach (array_keys($expectedStrings) as $expectedString) {
                    if (str_contains($string, $expectedString)) {
                        $expectedStrings[$expectedString] = true;
                        return true;
                    }
                }
                return true;
            }));

        $listener = $this->createListener($requiresPackageName = true);

        $this->assertNull($listener($this->event));
        $this->assertAllExpectedOutputEmitted($expectedStrings);
    }

    public function testMarksConfigurationIncompleteIfRemoteNameDiscoveryFails()
    {
        $invokedCounter = $this->exactly(2);
        $this->dispatcher
            ->expects($invokedCounter)
            ->method('dispatch')
            ->will($this->returnCallback(function ($event) use ($invokedCounter) {
                $invocationCount = $invokedCounter->getInvocationCount();
                if (1 === $invocationCount) {
                    TestCase::assertInstanceOf(ConfigDiscovery::class, $event);
                    return $this->discovery;
                }
                if (2 === $invocationCount) {
                    TestCase::assertInstanceOf(RemoteNameDiscovery::class, $event);
                    return new RemoteNameDiscovery($this->input, $this->output, $this->config, new QuestionHelper());
                }
                return null;
            }));
        $expectedStrings = [
            'Unable to determine Git remote' => false,
            'do one of the following'        => false,
            'Add a "remote" setting'         => false,
            '--remote option'                => false,
        ];
        $this->output
            ->expects($this->atLeast(4))
            ->method('writeln')
            ->with($this->callback(function (string $string) use (&$expectedStrings): bool {
                foreach (array_keys($expectedStrings) as $expectedString) {
                    if (str_contains($string, $expectedString)) {
                        $expectedStrings[$expectedString] = true;
                        return true;
                    }
                }
                return true;
            }));

        $this->event->expects($this->never())->method('discoveredConfiguration');
        $this->event->expects($this->once())->method('configurationIncomplete');

        $listener = $this->createListener($requiresPackageName = false, $requiresRemoteName = true);

        $this->assertNull($listener($this->event));
        $this->assertAllExpectedOutputEmitted($expectedStrings);
    }

    private function assertAllExpectedOutputEmitted(array $expectedStrings): void
    {
        $notFound = [];
        foreach ($expectedStrings as $string => $found) {
            if (! $found) {
                $notFound[] = $string;
            }
        }

        if (count($notFound) === 0) {
            return;
        }

        $this->fail(sprintf('One or more expected output strings were not emitted: %s', implode(', ', $notFound)));
    }
}
