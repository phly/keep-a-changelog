<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use InvalidArgumentException;
use Phly\KeepAChangelog\Provider;

class InvalidProviderException extends InvalidArgumentException
{
    public static function forProvider(string $provider, array $allowedProviders) : self
    {
        return new self(sprintf(
            'Unknown provider "%s"; must be one of (%s)',
            $provider,
            implode(', ', $allowedProviders)
        ));
    }

    public static function forIncompleteProvider(Provider\ProviderInterface $provider) : self
    {
        return new self(sprintf(
            'Provider %s does not implement %s and thus cannot be used to determine where to push tags;'
            . ' please implement %s',
            gettype($provider),
            Provider\ProviderNameProviderInterface::class,
            Provider\ProviderNameProviderInterface::class
        ));
    }

    public static function forInvalidProviderDomain(string $domain) : self
    {
        return new self(sprintf(
            'Domain "%s" is invalid, and cannot be used to with changelog providers %s',
            $domain
        ));
    }
}
