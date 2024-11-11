<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageNameDiscoveryTest extends TestCase
{
    private Config $config;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private Config\PackageNameDiscovery $event;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->event  = new Config\PackageNameDiscovery($this->input, $this->output, $this->config);
    }

    public function testIsAStoppableEvent()
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $this->event);
    }

    public function testIsNotStoppedByDefaultIfConfigDoesNotHaveAnAssociatedPackage()
    {
        $this->assertFalse($this->event->isPropagationStopped());
    }

    public function testIsNotStoppedByDefaultIfConfigHasAnAssociatedPackage()
    {
        $this->config->setPackage('some/package');
        $this->assertTrue($this->event->isPropagationStopped());
    }

    public function testIndicatesPackageIsNotFoundIfConfigDoesNotHaveAnAssociatedPackage()
    {
        $this->assertFalse($this->event->packageWasFound());
    }

    public function testIndicatesPackageIsFoundIfConfigHasAnAssociatedPackage()
    {
        $this->config->setPackage('some/package');
        $this->assertTrue($this->event->packageWasFound());
    }

    public function testMarkingPackageFoundSetsPackageInConfigAndStopsPropagation()
    {
        $this->event->foundPackage('some/package');

        $this->assertTrue($this->event->isPropagationStopped());
        $this->assertTrue($this->event->packageWasFound());
        $this->assertSame('some/package', $this->config->package());
    }
}
