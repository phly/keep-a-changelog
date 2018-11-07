<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use InvalidArgumentException;

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
}
