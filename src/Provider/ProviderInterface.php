<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

interface ProviderInterface
{
    /**
     * @param string $tagName
     * @param string $package
     * @param string $version
     * @param string $changelog
     * @return bool
     */
    public function createLocalTag(string $tagName, string $package, string $version, string $changelog) : bool;

    /**
     * @param string $package
     * @param string $releaseName
     * @param string $tagName
     * @param string $changelog
     * @param string $token
     * @return null|string
     */
    public function createRelease(
        string $package,
        string $releaseName,
        string $tagName,
        string $changelog,
        string $token
    ) : ?string;

    /**
     * @return string
     */
    public function getRepositoryUrlRegex() : string;

    /**
     * @param string $package
     * @param int $pr
     * @return string
     */
    public function generatePullRequestLink(string $package, int $pr) : string;
}
