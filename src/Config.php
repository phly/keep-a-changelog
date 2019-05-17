<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

class Config
{
    /** @var string */
    private $changelogFile = 'CHANGELOG.md';

    /** @var null|string */
    private $package;

    /** @var null|Provider\ProviderInterface */
    private $provider;

    /** @var Provider\ProviderList */
    private $providers;

    /** @var null|string */
    private $remote;

    public function __construct()
    {
        $this->providers = new Provider\ProviderList();
        $this->providers->set('github', new Provider\GitHub());
        $this->providers->set('gitlab', new Provider\GitLab());
    }

    public function changelogFile() : string
    {
        return $this->changelogFile;
    }

    public function package() : ?string
    {
        return $this->package;
    }

    public function provider() : Provider\ProviderInterface
    {
        if (! $this->provider instanceof Provider\ProviderInterface) {
            return $this->providers->get('github');
        }

        return $this->provider;
    }

    public function providers() : Provider\ProviderList
    {
        return $this->providers;
    }

    public function remote() : ?string
    {
        return $this->remote;
    }

    public function setChangelogFile(string $file) : void
    {
        $this->changelogFile = $file;
    }

    public function setPackage(string $package) : void
    {
        $this->package = $package;
    }

    public function setProvider(Provider\ProviderInterface $provider) : void
    {
        $this->provider = $provider;
    }

    public function setRemote(string $remote) : void
    {
        $this->remote = $remote;
    }
}
