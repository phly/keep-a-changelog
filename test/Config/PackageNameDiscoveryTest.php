<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageNameDiscoveryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->event  = new Config\PackageNameDiscovery(
            $this->input->reveal(),
            $this->output->reveal(),
            $this->config
        );
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
