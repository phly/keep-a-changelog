<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider\Exception;

use InvalidArgumentException;
use Phly\KeepAChangelog\Provider\ProviderInterface;

use function gettype;
use function sprintf;

class InvalidPackageNameException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forPackage(string $package, ProviderInterface $provider): self
    {
        return new self(sprintf(
            'Package name "%s" cannot be used with provider of type %s; please recheck',
            $package,
            gettype($provider)
        ));
    }
}
