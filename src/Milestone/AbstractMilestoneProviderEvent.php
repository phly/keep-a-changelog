<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Common\AbstractEvent;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;

use function gettype;
use function sprintf;

abstract class AbstractMilestoneProviderEvent extends AbstractEvent
{
    /** @var null|ProviderInterface|MilestoneAwareProviderInterface */
    protected $provider;

    public function provider(): ?ProviderInterface
    {
        return $this->provider;
    }

    public function discoveredProvider(ProviderInterface $provider): void
    {
        $this->provider = $provider;
    }

    public function providerIsIncomplete(): void
    {
        $this->failed = true;
        $output       = $this->output();

        $output->writeln('<error>Provider incapable of creating milestone</error>');
        $output->writeln('The provider as currently configured is incapable of creating a milestone.');
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

    public function providerIncapableOfMilestones(): void
    {
        $this->failed = true;
        $output       = $this->output();

        $output->writeln('<error>Unable to create milestone!</error>');
        $output->writeln(sprintf(
            'Provider of type "%s" is not milestone aware, and thus cannot be used to create a new milestone.',
            gettype($this->provider)
        ));
    }
}
