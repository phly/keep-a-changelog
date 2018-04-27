<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

class GitLab implements ProviderInterface
{
    public function createLocalTag(string $tagName, string $package, string $version, string $changelog) : bool
    {
        $command = sprintf('git tag -s -m "%s %s" %s', $package, $version, $tagName);
        system($command, $return);
        return 0 === $return;
    }
}
