<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\TestAsset;

use Phly\KeepAChangelog\Provider\ProviderInterface;

class Provider implements ProviderInterface
{
    /** @param null|string */
    public $package;

    /** @param null|string */
    public $token;

    /** @param null|string */
    public $url;

    public function canCreateRelease() : bool
    {
        return false;
    }

    public function canGenerateLinks() : bool
    {
        return false;
    }

    public function createRelease(
        string $releaseName,
        string $tagName,
        string $changelog
    ) : ?string {
        return null;
    }

    public function generateIssueLink(int $issueIdentifier) : string
    {
        return '';
    }

    public function generatePatchLink(int $patchIdentifier) : string
    {
        return '';
    }

    public function setPackageName(string $package) : void
    {
        $this->package = $package;
    }

    public function setToken(string $token) : void
    {
        $this->token = $token;
    }

    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }
}
