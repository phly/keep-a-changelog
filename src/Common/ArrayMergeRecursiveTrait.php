<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

trait ArrayMergeRecursiveTrait
{
    /**
     * Merge two arrays together.
     *
     * If an integer key exists in both arrays and preserveNumericKeys is false, the value
     * from the second array will be appended to the first array. If both values are arrays, they
     * are merged together, else the value of the second array overwrites the one of the first array.
     *
     * Implementation from zendframework/zend-stdlib, `Zend\Stdlib\ArrayUtils::merge()`
     *
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     */
    public function arrayMergeRecursive(array $a, array $b, bool $preserveNumericKeys = false) : array
    {
        foreach ($b as $key => $value) {
            if (! isset($a[$key]) && ! array_key_exists($key, $a)) {
                $a[$key] = $value;
                continue;
            }

            if (! $preserveNumericKeys && is_int($key)) {
                $a[] = $value;
                continue;
            }

            if (is_array($value) && is_array($a[$key])) {
                $a[$key] = $this->arrayMergeRecursive($a[$key], $value, $preserveNumericKeys);
                continue;
            }

            $a[$key] = $value;
        }

        return $a;
    }
}
