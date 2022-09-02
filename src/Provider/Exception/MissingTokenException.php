<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider\Exception;

use Phly\KeepAChangelog\Exception\ExceptionInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use RuntimeException;

use function gettype;
use function sprintf;

class MissingTokenException extends RuntimeException implements ExceptionInterface
{
    public static function for(ProviderInterface $provider): self
    {
        return new self(sprintf(
            'Unable to create release using provider of type %s due to missing API token',
            gettype($provider)
        ));
    }
}
