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

    private const DEFAULT_DOMAINS = [
        self::PROVIDER_GITHUB => 'github.com',
        self::PROVIDER_GITLAB => 'gitlab.com',
    ];

    /** @var string */
    private $domain;

    /** @var string */
    private $provider;

    /** @var string */
    private $token;

    /**
     * @throws Exception\InvalidProviderException if the $provider is unknown.
     */
    public function __construct(
        string $token = '',
        string $provider = self::PROVIDER_GITHUB,
        string $domain = ''
    ) {
        $this->token = $token;

        $this->validateProvider($provider);
        $this->provider = $provider;

        $domain = $domain ?: self::DEFAULT_DOMAINS[$provider];
        $this->validateDomain($domain);
        $this->domain = $domain;
    }

    public function domain() : string
    {
        return $this->domain;
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
    public function withDomain(string $domain) : self
    {
        $domain = $domain ?: self::DEFAULT_DOMAINS[$this->provider];
        $this->validateDomain($domain);
        $config = clone $this;
        $config->domain = $domain;
        return $config;
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
            'domain'   => $this->domain,
        ];
    }

    /**
     * @throws Exception\InvalidProviderException
     */
    private function validateDomain(string $domain) : void
    {
        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw Exception\InvalidProviderException::forInvalidProviderDomain($domain);
        }
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
