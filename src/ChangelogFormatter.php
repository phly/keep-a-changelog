<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

class ChangelogFormatter
{
    public function format(string $changelog)
    {
        return preg_replace_callback(
            '/^\#\#\# (?<heading>Added|Changed|Deprecated|Removed|Fixed)/m',
            function (array $matches) {
                return sprintf(
                    "%s\n%s",
                    $matches['heading'],
                    str_repeat('-', strlen($matches['heading']))
                );
            },
            $changelog
        );
    }
}
