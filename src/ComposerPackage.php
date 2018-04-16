<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use stdClass;

class ComposerPackage
{
    public function getName(string $path) : string
    {
        $composer = $this->parsePackage($path);

        if (! isset($composer->name)) {
            throw Exception\PackageNotFoundException::at($path);
        }

        return $composer->name;
    }

    private function parsePackage(string $path) : stdClass
    {
        $path .= '/composer.json';
        if (! file_exists($path)) {
            throw Exception\ComposerNotFoundException::at($path);
        }

        $contents = file_get_contents($path);
        return json_decode($contents);
    }
}
