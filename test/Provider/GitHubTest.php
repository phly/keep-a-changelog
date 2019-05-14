<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Provider;

use Phly\KeepAChangelog\Provider\Exception;
use Phly\KeepAChangelog\Provider\GitHub;
use PHPUnit\Framework\TestCase;

class GitHubTest extends TestCase
{
    public function setUp()
    {
        $this->github = new GitHub();
    }

    public function testReportsCannotCreateReleaseByDefault()
    {
        $this->assertFalse($this->github->canCreateRelease());
    }

    public function testReportsGenerateLinksByDefault()
    {
        $this->assertFalse($this->github->canGenerateLinks());
    }

    public function testSettingPackageNameAllowsLinkGenerationButNotReleaseCreation()
    {
        $this->github->setPackageName('phly/keep-a-changelog');
        $this->assertTrue($this->github->canGenerateLinks());
        $this->assertFalse($this->github->canCreateRelease());
    }

    public function testSettingPackageNameAndTOkenAllowsLinkGenerationAndReleaseCreation()
    {
        $this->github->setPackageName('phly/keep-a-changelog');
        $this->github->setToken('this-is-a-token');
        $this->assertTrue($this->github->canGenerateLinks());
        $this->assertTrue($this->github->canCreateRelease());
    }

    public function testHasADefaultDomain()
    {
        $this->assertSame('github.com', $this->github->domain());
    }

    public function testDomainIsMutable()
    {
        $this->github->setUrl('https://git.custom-domain.com');
        $this->assertSame('git.custom-domain.com', $this->github->domain());
    }

    public function invalidUrls() : iterable
    {
        yield 'bare-word'   => ['invalid'];
        yield 'scheme-only' => ['https://'];
    }

    /**
     * @dataProvider invalidUrls
     */
    public function testSettingUrlRaisesExceptionForInvalidUrl(string $url)
    {
        $this->expectException(Exception\InvalidUrlException::class);
        $this->github->setUrl($url);
    }

    public function invalidPackageNames() : iterable
    {
        yield 'empty'                 => [''];
        yield 'invalid-vendor'        => ['@phly'];
        yield 'vendor-only'           => ['phly'];
        yield 'invalid-repo'          => ['phly/@invalid'];
        yield 'invalid-subgroup'      => ['phly/subgroup/package'];
    }

    /**
     * @dataProvider invalidPackageNames
     */
    public function testGeneratePullRequestLinkRaisesExceptionForInvalidPackageNames(string $package)
    {
        $this->expectException(Exception\InvalidPackageNameException::class);
        $this->github->setPackageName($package);
    }

    public function packageAndPatchLinks() : iterable
    {
        // @phpcs:disable
        yield 'typical'               => ['phly/keep-a-changelog', 42, '[#42](https://github.com/phly/keep-a-changelog/pull/42)'];
        yield 'typical-underscore'    => ['phly/keep_a_changelog', 42, '[#42](https://github.com/phly/keep_a_changelog/pull/42)'];
        // @phpcs:enable
    }

    /**
     * @dataProvider packageAndPatchLinks
     */
    public function testGeneratePatchLinkCreatesExpectedLinks(string $package, int $pr, string $expected)
    {
        $this->github->setPackageName($package);
        $link = $this->github->generatePatchLink($pr);
        $this->assertSame($expected, $link);
    }

    public function packageAndIssueLinks() : iterable
    {
        // @phpcs:disable
        yield 'typical'               => ['phly/keep-a-changelog', 42, '[#42](https://github.com/phly/keep-a-changelog/issues/42)'];
        yield 'typical-underscore'    => ['phly/keep_a_changelog', 42, '[#42](https://github.com/phly/keep_a_changelog/issues/42)'];
        // @phpcs:enable
    }

    /**
     * @dataProvider packageAndIssueLinks
     */
    public function testGenerateIssueLinkCreatesExpectedLinks(string $package, int $issue, string $expected)
    {
        $this->github->setPackageName($package);
        $link = $this->github->generateIssueLink($issue);
        $this->assertSame($expected, $link);
    }

    public function testCreateReleaseRaisesExceptionIfPackageIsMissing()
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->github->createRelease('some/package 1.2.3', 'v1.2.3', 'the changelog');
    }

    public function testCreateReleaseRaisesExceptionIfTokenIsMissing()
    {
        $this->github->setPackageName('some/package');
        $this->expectException(Exception\MissingTokenException::class);
        $this->github->createRelease('some/package 1.2.3', 'v1.2.3', 'the changelog');
    }
}
