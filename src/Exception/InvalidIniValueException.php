<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Exception;

use InvalidArgumentException;

use function gettype;
use function sprintf;

class InvalidIniValueException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $value
     */
    public static function forValue($value, string $name) : self
    {
        return new self(sprintf(
            'Cannot cast value of type %s associated with key %s',
            gettype($value),
            $name
        ));
    }

    public static function forNestedArrayValue(string $name) : self
    {
        return new self(sprintf(
            'Cannot handle nested list values when generating INI formats; discovered for key %s',
            $name
        ));
    }
}
