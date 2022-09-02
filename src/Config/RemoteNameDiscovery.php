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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class RemoteNameDiscovery implements IOInterface, StoppableEventInterface
{
    use IOTrait;

    /** @var bool */
    private $abort = false;

    /** @var Config */
    private $config;

    /** @var bool */
    private $gitRemoteResolutionFailed = false;

    /** @var QuestionHelper */
    private $questionHelper;

    /** @var bool */
    private $remoteFound = false;

    /** @var string[] */
    private $remotes = [];

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Config $config,
        QuestionHelper $questionHelper
    ) {
        $this->input          = $input;
        $this->output         = $output;
        $this->config         = $config;
        $this->questionHelper = $questionHelper;
    }

    public function isPropagationStopped(): bool
    {
        if ($this->abort) {
            return true;
        }

        if ($this->gitRemoteResolutionFailed) {
            return true;
        }

        return $this->remoteFound
            || null !== $this->config->remote();
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function questionHelper(): QuestionHelper
    {
        return $this->questionHelper;
    }

    public function remotes(): array
    {
        return $this->remotes;
    }

    public function remoteWasFound(): bool
    {
        return $this->remoteFound
            || null !== $this->config->remote();
    }

    public function foundRemote(string $remote): void
    {
        $this->config->setRemote($remote);
        $this->remoteFound = true;
    }

    public function setRemotes(array $remotes): void
    {
        $this->remotes = $remotes;
    }

    public function reportNoMatchingGitRemoteFound(string $domain, string $package): void
    {
        $this->gitRemoteResolutionFailed = true;
        $output                          = $this->output();

        $output->writeln('<error>Cannot determine git remote!</error>');
        $output->writeln(sprintf(
            '- Do no remotes registered in your repository match the provider in use? ("%s")',
            $domain
        ));
        $output->writeln(sprintf(
            '- Do no remotes registered in your repository match the <package> provided? ("%s")',
            $package
        ));
    }

    public function abort(): void
    {
        $this->abort = true;
        $this->output()->writeln('<error>Aborted at user request</error>');
    }
}
