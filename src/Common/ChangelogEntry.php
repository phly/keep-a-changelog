<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use TypeError;
use UnexpectedValueException;

class ChangelogEntry
{
    /** @var string */
    private $contents = '';

    /** @var null|int */
    private $index = null;

    /** @var int */
    private $length = 0;

    public function __get(string $name)
    {
        if (! property_exists($this, $name)) {
            throw new UnexpectedValueException(sprintf(
                'The property "%s" does not exist for class "%s"',
                $name,
                gettype($this)
            ));
        }

        return $this->$name;
    }

    public function __set(string $name, $value)
    {
        switch ($name) {
            case 'contents':
                if (! is_string($value)) {
                    throw new TypeError(sprintf(
                        'Property %s expects a string; received %s',
                        $name,
                        gettype($value)
                    ));
                }
                $this->$name = $value;
                break;
            case 'index':
                if (null !== $value && ! is_int($value)) {
                    throw new TypeError(sprintf(
                        'Property %s expects an integer or null value; received %s',
                        $name,
                        gettype($value)
                    ));
                }
                $this->$name = $value;
                break;
            case 'length':
                if (! is_int($value)) {
                    throw new TypeError(sprintf(
                        'Property %s expects an integer; received %s',
                        $name,
                        gettype($value)
                    ));
                }
                $this->$name = $value;
                break;
            default:
                throw new UnexpectedValueException(sprintf(
                    'The property "%s" does not exist for class "%s"',
                    $name,
                    gettype($this)
                ));
        }
    }
}
