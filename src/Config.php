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

    public function __construct(string $token = '', string $provider = self::PROVIDER_GITHUB)
    {
        $this->token = $token;
        $this->provider = $provider;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function withProvider(string $provider) : self
    {
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
}
