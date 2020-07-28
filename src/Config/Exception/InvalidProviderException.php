<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config\Exception;

use InvalidArgumentException;
use Phly\KeepAChangelog\Provider\ProviderInterface;

use function implode;
use function sprintf;

class InvalidProviderException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forMissingClassName(string $name, string $configType): self
    {
        return new self(sprintf(
            'Error parsing %s: provider "%s" is missing a "class" setting',
            $configType,
            $name
        ));
    }

    public static function forMissingClass(string $name, string $configType): self
    {
        return new self(sprintf(
            'Error parsing %s: cannot autoload the provider "%s"',
            $configType,
            $name
        ));
    }

    public static function forInvalidClass(string $name, string $class, string $configType): self
    {
        return new self(sprintf(
            'Error parsing %s: class name "%s" associated with provider "%s" does not implement %s',
            $configType,
            $class,
            $name,
            ProviderInterface::class
        ));
    }

    public static function forMissingProvider(string $name, array $known, string $configType): self
    {
        return new self(sprintf(
            'Error parsing %s: selected default provider "%s" is not configured. Known providers: %s',
            $configType,
            $name,
            implode(', ', $known)
        ));
    }
}
