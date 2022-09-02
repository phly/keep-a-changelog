<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\ConfigDiscovery;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDiscoveryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreatesConfigInstanceWhenInstantiated()
    {
        $discovery = new ConfigDiscovery(
            $this->prophesize(InputInterface::class)->reveal(),
            $this->prophesize(OutputInterface::class)->reveal()
        );

        $this->assertInstanceOf(Config::class, $discovery->config());
    }
}
