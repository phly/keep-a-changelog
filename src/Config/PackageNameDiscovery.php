<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\Common\IOTrait;
use Phly\KeepAChangelog\Config;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageNameDiscovery implements IOInterface, StoppableEventInterface
{
    use IOTrait;

    /** @var Config */
    private $config;

    /** @var bool */
    private $packageFound = false;

    public function __construct(InputInterface $input, OutputInterface $output, Config $config)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->config = $config;
    }

    public function isPropagationStopped(): bool
    {
        return $this->packageFound
            || null !== $this->config->package();
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function packageWasFound(): bool
    {
        return $this->isPropagationStopped();
    }

    public function foundPackage(string $package): void
    {
        $this->config->setPackage($package);
        $this->packageFound = true;
    }
}
