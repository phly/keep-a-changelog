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
    const PROVIDER_GITHUB = 'github';
    const PROVIDER_GITLAB = 'gitlab';

    const PROVIDERS = [
        self::PROVIDER_GITHUB,
//        self::PROVIDER_GITLAB // Will be enabled after PR #24 is accepted
    ];

    /** @var string */
    private $provider;
    /** @var string */
    private $token;

    /**
     * Config constructor.
     * @param string $token
     * @param string $provider
     */
    public function __construct(string $token = '', string $provider = self::PROVIDER_GITHUB)
    {
        $this->token = $token;
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function provider(): string
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * @param string $provider
     * @return Config
     */
    public function withProvider(string $provider) : self
    {
        $config = clone $this;
        $config->provider = $provider;
        return $config;
    }

    /**
     * @param string $token
     * @return Config
     */
    public function withToken(string $token) : self
    {
        $config = clone $this;
        $config->token = $token;
        return $config;
    }

    /**
     * @return array
     */
    public function getArrayCopy() : array
    {
        return [
            'token'    => $this->token,
            'provider' => $this->provider,
        ];
    }
}
