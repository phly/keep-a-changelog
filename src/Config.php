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
    private $changelogFile;

    /** @var null|string */
    private $package;

    /** @var null|Provider\ProviderSpec */
    private $provider;

    /** @var string */
    private $providerName = 'github';

    /** @var Provider\ProviderList */
    private $providers;

    /** @var null|string */
    private $remote;

    public function __construct()
    {
        $this->changelogFile = realpath(getcwd()) . '/CHANGELOG.md';

        $githubSpec = new Provider\ProviderSpec('github');
        $githubSpec->setClassName(Provider\GitHub::class);

        $gitlabSpec = new Provider\ProviderSpec('gitlab');
        $gitlabSpec->setClassName(Provider\GitLab::class);

        $this->providers = new Provider\ProviderList();
        $this->providers->add($githubSpec);
        $this->providers->add($gitlabSpec);
    }

    public function changelogFile() : string
    {
        return $this->changelogFile;
    }

    public function package() : ?string
    {
        return $this->package;
    }

    public function provider() : Provider\ProviderSpec
    {
        if (! $this->provider) {
            return $this->providers->get($this->providerName);
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
        $this->provider()->setPackage($package);
    }

    public function setProviderName(string $providerName) : void
    {
        $this->providerName = $providerName;
        $this->provider     = $this->providers->get($providerName);
        if ($this->package) {
            $this->provider->setPackage($this->package);
        }
    }

    public function setRemote(string $remote) : void
    {
        $this->remote = $remote;
    }
}
