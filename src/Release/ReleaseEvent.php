<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\ConfigurableEventInterface;
use Phly\KeepAChangelog\IOTrait;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ReleaseEvent implements ConfigurableEventInterface
{
    use IOTrait;

    /**
     * The changelog entry associated with the version being released.
     *
     * @var null|string
     */
    private $changelog;

    /** @var null|Config */
    private $config;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var bool */
    private $failed = false;

    /** @var null|ProviderInterface */
    private $provider;

    /** @var null|string */
    private $rawChangelog;

    /** @var null|string */
    private $releaseName;

    /** @var string */
    private $tagName;

    /** @var string */
    private $version;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        EventDispatcherInterface $dispatcher
    ) {
        $this->input      = $input;
        $this->output     = $output;
        $this->dispatcher = $dispatcher;
        $this->version    = $input->getArgument('version');
        $this->tagName    = $input->getOption('tag-name') ?: $this->version;
    }

    public function isPropagationStopped() : bool
    {
        return $this->failed;
    }

    public function failed() : bool
    {
        return $this->failed;
    }

    public function changelog() : ?string
    {
        if ($this->changelog) {
            return $this->changelog;
        }

        if ($this->rawChangelog) {
            return $this->rawChangelog;
        }

        return null;
    }

    public function config() : ?Config
    {
        return $this->config;
    }

    public function dispatcher() : EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function provider() : ?ProviderInterface
    {
        return $this->provider;
    }

    public function releaseName() : ?string
    {
        return $this->releaseName;
    }

    public function tagName() : string
    {
        return $this->tagName;
    }

    public function version() : string
    {
        return $this->version;
    }

    public function missingConfiguration() : bool
    {
        return null === $this->config;
    }

    public function discoveredConfiguration(Config $config) : void
    {
        $this->config = $config;
    }

    public function setRawChangelog(string $changelog) : void
    {
        if ($this->changelog) {
            return;
        }
        $this->rawChangelog = $changelog;
    }

    public function discoveredChangelog(string $changelog) : void
    {
        $this->changelog = $changelog;
    }

    public function setReleaseName(string $releaseName) : void
    {
        $this->releaseName = $releaseName;
    }

    public function releaseCreated(string $release) : void
    {
        $this->output()->writeln(sprintf('<info>Created %s<info>', $release));
    }

    public function changelogFileIsUnreadable(string $changelogFile) : void
    {
        $this->failed = true;
        $this->output()->writeln(sprintf(
            '<error>Changelog file "%s" is unreadable.</error>',
            $changelogFile
        ));
    }

    public function errorParsingChangelog(string $changelogFile, Throwable $e) : void
    {
        $this->failed = true;
        $output = $this->output();
        $output->writeln(sprintf(
            '<error>An error occurred parsing the changelog file "%s" for the release "%s":</error>',
            $changelogFile,
            $this->version
        ));
        $output->writeln($e->getMessage());
    }

    public function configurationIncomplete() : void
    {
        $this->failed = true;
    }

    public function providerIsIncomplete() : void
    {
        $this->failed = true;
        $output       = $this->output();

        $output->writeln('<error>Provider incapable of release</error>');
        $output->writeln('The provider as currently configured is incapable of performing a release.');
        $output->writeln(
            'A fully configured provider includes the class name,'
            . ' an authentication token, and a base URL for API calls'
            . ' (which may be hard-coded into the class, but may be'
            . ' configurable). You may provide them via a combination'
            . ' of any of the following:'
        );
        $output->writeln(
            '- The file $XDG_CONFIG_HOME/keep-a-changelog.ini (usually'
            . ' $HOME/.config/keep-a-changelog.ini)'
        );
        $output->writeln('- The file ./.keep-a-changelog.ini');
        $output->writeln(
            '- The option --provider, with a value pointing to a provider'
            . ' fully configured in one of the above files'
        );
        $output->writeln(sprintf(
            '- The option --provider-class, resolving to an instance of %s',
            ProviderInterface::class
        ));
        $output->writeln(
            '- The options --provider-url and --provider-token can supply'
            . ' the provider URL and authentication token, respectively,'
            . ' if not specified in the provider instance or configuration files.'
        );
    }

    public function discoveredProvider(ProviderInterface $provider) : void
    {
        $this->provider = $provider;
    }

    public function couldNotFindTag() : void
    {
        $this->failed = true;
        $this->output()->writeln(sprintf(
            '<error>No tag matching the name "%s" was found!</error>',
            $this->tagName
        ));
    }

    public function taggingFailed() : void
    {
        $this->failed = true;
        $output = $this->output();
        $output->writeln('<error>Error pushing tag to remote!');
        $output->writeln('Please check the output for details.');
    }

    public function errorCreatingRelease(Throwable $e) : void
    {
        $this->failed = true;
        $output       = $this->output();

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
        $this->failed = true;
        $output       = $this->output();

        $output->writeln('<error>Error creating release!</error>');
        $output->writeln(sprintf(
            'Provider of type "%s" was able to make the API call necessary to create the release,'
            . ' but did not get back the expected result.'
            . ' You will need to manually create the release.',
            gettype($this->provider)
        ));
    }
}
