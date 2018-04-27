<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

class GitHub implements ProviderInterface
{
    public function createLocalTag(string $tagName, string $package, string $version, string $changelog) : bool
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($tempFile, sprintf("%s %s\n\n%s", $package, $version, $changelog));

        $command = sprintf('git tag -s -F %s %s', $tempFile, $tagName);
        system($command, $return);

        unlink($tempFile);

        return 0 === $return;
    }
}
