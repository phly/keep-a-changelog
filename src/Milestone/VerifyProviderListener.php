<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;

class VerifyProviderListener
{
    public function __invoke(AbstractMilestoneProviderEvent $event): void
    {
        $config       = $event->config();
        $providerSpec = $config->provider();

        if (! $providerSpec->isComplete()) {
            $event->providerIsIncomplete();
            return;
        }

        $provider = $providerSpec->createProvider();

        if (! $provider instanceof MilestoneAwareProviderInterface) {
            $event->providerIncapableOfMilestones();
            return;
        }

        $event->discoveredProvider($provider);
    }
}
