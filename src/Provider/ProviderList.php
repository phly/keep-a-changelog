<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use function array_keys;

class ProviderList
{
    /** @var array array<string, ProviderSpec> */
    private $providers = [];

    public function has(string $name): bool
    {
        return isset($this->providers[$name]);
    }

    public function get(string $name): ?ProviderSpec
    {
        return $this->providers[$name] ?? null;
    }

    public function add(ProviderSpec $provider): void
    {
        $this->providers[$provider->name()] = $provider;
    }

    public function listKnownTypes(): array
    {
        return array_keys($this->providers);
    }
}
