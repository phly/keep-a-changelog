<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\IOTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function getcwd;
use function getenv;
use function realpath;
use function sprintf;

class ValidateRequirementsEvent implements StoppableEventInterface
{
    use IOTrait;

    /**
     * Path where global config is kept.
     *
     * This property exists solely for testing. When set, the value will be used
     * instead of getenv('HOME').
     *
     * @var null|string
     */
    public $globalPath;

    /**
     * Path where global config is kept.
     *
     * This property exists solely for testing. When set, the value will be used
     * instead of realpath(getcwd())
     *
     * @var null|string
     */
    public $localPath;

    /** @var null|Config */
    private $config;

    /** @var bool */
    private $tagDoesNotExist = false;

    /** @var string */
    private $tagName;

    /** @var bool */
    private $tokenNotFound = false;

    /** @var string */
    private $version;

    public function __construct(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input   = $input;
        $this->output  = $output;
        $this->version = $input->getArgument('version');
        $this->tagName = $input->getOption('tagname') ?: $this->version;
    }

    public function isPropagationStopped() : bool
    {
        if ($this->tagDoesNotExist) {
            return true;
        }

        if ($this->tokenNotFound) {
            return true;
        }

        if ($this->config && $this->config->token()) {
            return true;
        }

        return false;
    }

    public function version() : string
    {
        return $this->version;
    }

    public function tagName() : string
    {
        return $this->tagName;
    }

    public function requirementsMet() : bool
    {
        return ! $this->tagDoesNotExist
            && ! $this->tokenNotFound
            && $this->config
            && $this->config->token();
    }

    public function couldNotFindTag() : void
    {
        $this->tagDoesNotExist = true;
        $this->output()->writeln(sprintf(
            '<error>No tag matching the name "%s" was found!</error>',
            $this->tagName
        ));
    }

    public function setConfig(Config $config) : void
    {
        $this->config = $config;
    }

    public function config() : ?Config
    {
        return $this->config;
    }

    public function tokenNotFound() : void
    {
        $this->tokenNotFound = true;
        $configFile          = $this->getConfigFile();
        $output              = $this->output();

        $output->writeln(sprintf(
            '<error>No token provided, and could not find it in the config file %s</error>',
            $configFile
        ));
        $output->writeln(
            'Please provide the --token option, or create the config file with the config command'
        );
    }

    private function getConfigFile() : string
    {
        $useGlobal  = $this->input()->getOption('global') ?: false;
        $globalPath = $this->globalPath ?: getenv('HOME');
        $localPath  = $this->localPath ?: realpath(getcwd());

        return $useGlobal
            ? sprintf('%s/.keep-a-changelog/config.ini', $globalPath)
            : sprintf('%s/.keep-a-changelog.ini', $localPath);
    }
}
