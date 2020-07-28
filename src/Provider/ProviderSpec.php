<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use function class_exists;
use function class_implements;
use function in_array;

class ProviderSpec
{
    /** @var null|string */
    private $className;

    /**
     * Name of the provider as found in configuration
     *
     * @var string
     */
    private $name;

    /** @var null|string */
    private $package;

    /** @var null|string */
    private $token;

    /** @var null|string */
    private $url;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function isComplete(): bool
    {
        return $this->className
            && class_exists($this->className)
            && in_array(ProviderInterface::class, class_implements($this->className), true);
    }

    public function createProvider(): ProviderInterface
    {
        $class    = $this->className;
        $provider = new $class();

        if ($this->package) {
            $provider->setPackageName($this->package);
        }

        if ($this->token) {
            $provider->setToken($this->token);
        }

        if ($this->url) {
            $provider->setUrl($this->url);
        }

        return $provider;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function setPackage(string $package): void
    {
        $this->package = $package;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
