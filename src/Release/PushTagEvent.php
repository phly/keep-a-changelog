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
use Phly\KeepAChangelog\Provider;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushTagEvent implements StoppableEventInterface
{
    use IOTrait;

    /** @var bool */
    private $abortRelease = false;

    /** @var Config */
    private $config;

    /** @var bool */
    private $failed = false;

    /** @var bool */
    private $gitRemoteResolutionFailed = false;

    /** @var null|string */
    private $gitRemote;

    /** @var string[] */
    private $gitRemotes = [];

    /** @var bool */
    private $invalidProviderDetected = false;

    /** @var QuestionHelper */
    private $questionHelper;

    /** @var bool */
    private $success = false;

    /** @var string */
    private $tagName;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        Config $config,
        string $tagName
    ) {
        $this->input          = $input;
        $this->output         = $output;
        $this->questionHelper = $questionHelper;
        $this->config         = $config;
        $this->tagName        = $tagName;
    }

    public function isPropagationStopped() : bool
    {
        if ($this->gitRemoteResolutionFailed) {
            return true;
        }

        if ($this->invalidProviderDetected) {
            return true;
        }

        if ($this->abortRelease) {
            return true;
        }

        if ($this->failed) {
            return true;
        }

        if ($this->success) {
            return true;
        }

        return false;
    }

    public function config() : Config
    {
        return $this->config;
    }

    public function questionHelper() : QuestionHelper
    {
        return $this->questionHelper;
    }

    public function tagName() : string
    {
        return $this->tagName;
    }

    public function wasPushed() : bool
    {
        return $this->success;
    }

    public function gitRemoteResolutionFailed() : void
    {
        $this->gitRemoteResolutionFailed = true;
        $output = $this->output();
        $output->writeln('<error>Cannot determine remote to which to push tag!</error>');
        $output->writeln(
            'The command "git remote -v" had a non-zero exit status; verify the command works, and try again.'
        );
    }

    public function setRemotes(array $remotes) : void
    {
        $this->gitRemotes = $remotes;
    }

    public function remotes() : array
    {
        return $this->gitRemotes;
    }

    public function setRemote(string $remote) : void
    {
        $this->gitRemote = $remote;
    }

    public function remote() : ?string
    {
        return $this->gitRemote;
    }

    public function invalidProviderDetected(string $providerType) : void
    {
        $this->invalidProviderDetected = true;
        $this->output()->writeln(sprintf(
            '<error>Provider of type %s does not implement %s,</error>'
            . ' making it impossible to match to a git remote.',
            $providerType,
            Provider\ProviderNameProviderInterface::class
        ));
        $this->output()->writeln('Please use the --remote switch.');
    }

    public function reportNoMatchingGitRemoteFound(string $providerDomain, string $package) : void
    {
        $this->gitRemoteResolutionFailed = true;
        $output = $this->output();

        $output->writeln('<error>Cannot determine remote to which to push tag!</error>');
        $output->writeln(sprintf(
            '- Do no remotes registered in your repository match the provider in use? ("%s")',
            $providerDomain
        ));
        $output->writeln(sprintf(
            '- Do no remotes registered in your repository match the <package> provided? ("%s")',
            $package
        ));
    }

    public function abortRelease() : void
    {
        $this->abortRelease = true;
        $this->output()->writeln('<error>Aborted at user request</error>');
    }

    public function pushSucceeded() : void
    {
        $this->success = true;
    }

    public function pushFailed() : void
    {
        $this->failed = true;
        $output = $this->output();
        $output->writeln('<error>Error pushing tag to remote!');
        $output->writeln('Please check the output for details.');
    }
}
