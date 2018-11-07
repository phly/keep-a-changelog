<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

class Config
{
    public const PROVIDER_GITHUB = 'github';
    public const PROVIDER_GITLAB = 'gitlab';

    public const PROVIDERS = [
        self::PROVIDER_GITHUB,
        self::PROVIDER_GITLAB,
    ];

    /** @var string */
    private $provider;

    /** @var string */
    private $token;

    /**
     * @throws Exception\InvalidProviderException if the $provider is unknown.
     */
    public function __construct(string $token = '', string $provider = self::PROVIDER_GITHUB)
    {
        $this->token = $token;

        $this->validateProvider($provider);
        $this->provider = $provider;
    }

    public function provider() : string
    {
        return $this->provider;
    }

    public function token() : string
    {
        return $this->token;
    }

    /**
     * @throws Exception\InvalidProviderException
     */
    public function withProvider(string $provider) : self
    {
        $this->validateProvider($provider);
        $config = clone $this;
        $config->provider = $provider;
        return $config;
    }

    public function withToken(string $token) : self
    {
        $config = clone $this;
        $config->token = $token;
        return $config;
    }

    public function getArrayCopy() : array
    {
        return [
            'token'    => $this->token,
            'provider' => $this->provider,
        ];
    }

    /**
     * @throws Exception\InvalidProviderException
     */
    private function validateProvider(string $provider) : void
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            throw Exception\InvalidProviderException::forProvider($provider, self::PROVIDERS);
        }
    }
}
