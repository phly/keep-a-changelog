<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\Exception\InvalidProviderException;
use Phly\KeepAChangelog\Provider;
use Phly\KeepAChangelog\ReleaseCommand;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class LookupRemoteTest extends TestCase
{
    public function getMethod()
    {
        $command = new ReleaseCommand();
        $r = new ReflectionMethod($command, 'lookupRemote');
        $r->setAccessible(true);
        return function (...$arguments) use ($r, $command) {
            return $r->invokeArgs($command, $arguments);
        };
    }

    public function testLookupRemoteRaisesExceptionIfProviderCannotProvideName()
    {
        $provider = new class implements Provider\ProviderInterface {
            public function createRelease(
                string $package,
                string $releaseName,
                string $tagName,
                string $changelog,
                string $token
            ) : ?string {
            }

            public function getRepositoryUrlRegex() : string
            {
            }

            public function generatePullRequestLink(string $package, int $pr) : string
            {
            }
        };

        $this->expectException(InvalidProviderException::class);
        ($this->getMethod())($provider, 'phly/keep-a-changelog', []);
    }

    public function testLookupRemoteReturnsNullIfUnableToMatchRemotesToProviderAndPackage()
    {
        $provider = new class implements Provider\ProviderInterface, Provider\ProviderNameProvider {
            public function createRelease(
                string $package,
                string $releaseName,
                string $tagName,
                string $changelog,
                string $token
            ) : ?string {
            }

            public function getRepositoryUrlRegex() : string
            {
            }

            public function generatePullRequestLink(string $package, int $pr) : string
            {
            }

            public function getName() : string
            {
                return 'custom';
            }

            public function getDomainName() : string
            {
                return 'custom.develop';
            }
        };

        $remote = ($this->getMethod())($provider, 'phly/keep-a-changelog', [
            "origin\tgit://github.com/phly/keep-a-changelog.git\t(fetch)",
            "origin\tgit://github.com/phly/keep-a-changelog.git\t(push)",
            "upstream\thttps://github.com/phly/keep-a-changelog.git\t(fetch)",
            "upstream\thttps://github.com/phly/keep-a-changelog.git\t(push)",
            "custom\tgit@github.com:phly/keep-a-changelog.git\t(fetch)",
            "custom\tgit@github.com:phly/keep-a-changelog.git\t(push)",
        ]);

        $this->assertNull($remote);
    }

    public function testLookupRemoteReturnsRemoteNameForFirstRemoteThatMatchesProviderAndPackage()
    {
        $provider = new class implements Provider\ProviderInterface, Provider\ProviderNameProvider {
            public function createRelease(
                string $package,
                string $releaseName,
                string $tagName,
                string $changelog,
                string $token
            ) : ?string {
            }

            public function getRepositoryUrlRegex() : string
            {
            }

            public function generatePullRequestLink(string $package, int $pr) : string
            {
            }

            public function getName() : string
            {
                return 'custom';
            }

            public function getDomainName() : string
            {
                return 'custom.develop';
            }
        };

        $remote = ($this->getMethod())($provider, 'phly/keep-a-changelog', [
            "origin\tgit://github.com/phly/keep-a-changelog.git\t(fetch)",
            "origin\tgit://github.com/phly/keep-a-changelog.git\t(push)",
            "upstream\thttps://github.com/phly/keep-a-changelog.git\t(fetch)",
            "upstream\thttps://github.com/phly/keep-a-changelog.git\t(push)",
            "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(fetch)",
            "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(push)",
            "customHttps\thttps://custom.develop:phly/keep-a-changelog.git\t(fetch)",
            "customHttps\thttps://custom.develop:phly/keep-a-changelog.git\t(push)",
        ]);

        $this->assertSame('custom', $remote);
    }
}
