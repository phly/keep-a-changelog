<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\Common\IOTrait;
use Phly\KeepAChangelog\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDiscovery implements IOInterface
{
    use IOTrait;

    /** @var Config */
    private $config;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->config = new Config();
    }

    public function config(): Config
    {
        return $this->config;
    }
}
