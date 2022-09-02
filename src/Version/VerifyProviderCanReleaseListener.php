<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

class VerifyProviderCanReleaseListener
{
    public function __invoke(ReleaseEvent $event): void
    {
        $config       = $event->config();
        $providerSpec = $config->provider();

        if (! $providerSpec->isComplete()) {
            $event->providerIsIncomplete();
            return;
        }

        $provider = $providerSpec->createProvider();

        if (! $provider->canCreateRelease()) {
            $event->providerIsIncomplete();
            return;
        }

        $event->discoveredProvider($provider);
    }
}
