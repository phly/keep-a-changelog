<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\IOTrait;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderNameProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CreateReleaseEvent implements StoppableEventInterface
{
    use IOTrait;

    /** @var string */
    private $changelog;

    /** @var bool */
    private $error = false;

    /** @var string */
    private $package;

    /** @var ProviderInterface */
    private $provider;

    /** @var null|string */
    private $release;

    /** @var null|string */
    private $releaseName;

    /** @var string */
    private $token;

    /** @var string */
    private $version;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        ProviderInterface $provider,
        string $version,
        string $changelog,
        string $token
    ) {
        $this->input     = $input;
        $this->output    = $output;
        $this->provider  = $provider;
        $this->version   = $version;
        $this->changelog = $changelog;
        $this->token     = $token;
        $this->package   = $input->getArgument('package');
    }

    public function isPropagationStopped() : bool
    {
        if ($this->error) {
            return true;
        }

        if ($this->release) {
            return true;
        }

        return false;
    }

    public function wasCreated() : bool
    {
        return null !== $this->release;
    }

    public function changelog() : string
    {
        return $this->changelog;
    }

    public function package() : string
    {
        return $this->package;
    }

    public function provider() : ProviderInterface
    {
        return $this->provider;
    }

    public function token() : string
    {
        return $this->token;
    }

    public function version() : string
    {
        return $this->version;
    }

    public function setReleaseName(string $releaseName) : void
    {
        $this->releaseName = $releaseName;
    }

    public function releaseName() : ?string
    {
        return $this->releaseName;
    }

    public function releaseCreated(string $release) : void
    {
        $this->release = $release;
    }

    public function release() : ?string
    {
        return $this->release;
    }

    public function errorCreatingRelease(Throwable $e) : void
    {
        $this->error = true;
        $output      = $this->output();

        $output->writeln('<error>Error creating release!</error>');
        $output->writeln('The following error was caught when attempting to create the release:');
        $output->writeln(sprintf(
            "[%s: %d] %s\n%s",
            gettype($e),
            $e->getCode(),
            $e->getMessage(),
            $e->getTraceAsString()
        ));
    }

    public function unexpectedProviderResult() : void
    {
        $this->error = true;
        $output      = $this->output();

        $provider = $this->provider instanceof ProviderNameProviderInterface
            ? $this->provider->getName()
            : gettype($this->provider);

        $output->writeln('<error>Error creating release!</error>');
        $output->writeln(sprintf(
            'The provider "%s" was able to make the API call necessary to create the release,'
            . ' but did not get back the expected result.'
            . ' You will need to manually create the release.',
            $provider
        ));
    }
}
