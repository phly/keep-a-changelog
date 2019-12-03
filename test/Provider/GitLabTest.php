<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Provider;

use Phly\KeepAChangelog\Provider\Exception;
use Phly\KeepAChangelog\Provider\GitLab;
use PHPUnit\Framework\TestCase;

class GitLabTest extends TestCase
{
    protected function setUp() : void
    {
        $this->gitlab = new GitLab();
    }

    public function testReportsCannotCreateReleaseByDefault()
    {
        $this->assertFalse($this->gitlab->canCreateRelease());
    }

    public function testReportsGenerateLinksByDefault()
    {
        $this->assertFalse($this->gitlab->canGenerateLinks());
    }

    public function testSettingPackageNameAllowsLinkGenerationButNotReleaseCreation()
    {
        $this->gitlab->setPackageName('phly/keep-a-changelog');
        $this->assertTrue($this->gitlab->canGenerateLinks());
        $this->assertFalse($this->gitlab->canCreateRelease());
    }

    public function testSettingPackageNameAndTOkenAllowsLinkGenerationAndReleaseCreation()
    {
        $this->gitlab->setPackageName('phly/keep-a-changelog');
        $this->gitlab->setToken('this-is-a-token');
        $this->assertTrue($this->gitlab->canGenerateLinks());
        $this->assertTrue($this->gitlab->canCreateRelease());
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
        $this->gitlab->setUrl($url);
    }

    public function invalidPackageNames() : iterable
    {
        yield 'empty'                 => [''];
        yield 'invalid-vendor'        => ['@phly'];
        yield 'vendor-only'           => ['phly'];
        yield 'invalid-repo'          => ['phly/@invalid'];
        yield 'invalid-subgroup'      => ['phly/@invalid/package'];
        yield 'invalid-subgroup-repo' => ['phly/subgroup/@package'];
    }

    /**
     * @dataProvider invalidPackageNames
     */
    public function testGeneratePullRequestLinkRaisesExceptionForInvalidPackageNames(string $package)
    {
        $this->expectException(Exception\InvalidPackageNameException::class);
        $this->gitlab->setPackageName($package);
    }

    public function packageAndPatchLinks() : iterable
    {
        // @phpcs:disable
        yield 'typical'               => ['phly/keep-a-changelog', 42, '[!42](https://gitlab.com/phly/keep-a-changelog/merge_requests/42)'];
        yield 'typical-underscore'    => ['phly/keep_a_changelog', 42, '[!42](https://gitlab.com/phly/keep_a_changelog/merge_requests/42)'];
        yield 'subgroup'              => ['phly/cli/keep-a-changelog', 42, '[!42](https://gitlab.com/phly/cli/keep-a-changelog/merge_requests/42)'];
        // @phpcs:enable
    }

    /**
     * @dataProvider packageAndPatchLinks
     */
    public function testGeneratePatchLinkCreatesExpectedLink(string $package, int $pr, string $expected)
    {
        $this->gitlab->setPackageName($package);
        $link = $this->gitlab->generatePatchLink($pr);
        $this->assertSame($expected, $link);
    }

    public function packageAndIssueLinks() : iterable
    {
        // @phpcs:disable
        yield 'typical'               => ['phly/keep-a-changelog', 42, '[#42](https://gitlab.com/phly/keep-a-changelog/issues/42)'];
        yield 'typical-underscore'    => ['phly/keep_a_changelog', 42, '[#42](https://gitlab.com/phly/keep_a_changelog/issues/42)'];
        yield 'subgroup'              => ['phly/cli/keep-a-changelog', 42, '[#42](https://gitlab.com/phly/cli/keep-a-changelog/issues/42)'];
        // @phpcs:enable
    }

    /**
     * @dataProvider packageAndIssueLinks
     */
    public function testGenerateIssueLinkCreatesExpectedLink(string $package, int $issue, string $expected)
    {
        $this->gitlab->setPackageName($package);
        $link = $this->gitlab->generateIssueLink($issue);
        $this->assertSame($expected, $link);
    }

    public function testCreateReleaseRaisesExceptionIfPackageIsMissing()
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->gitlab->createRelease('some/package 1.2.3', 'v1.2.3', 'the changelog');
    }

    public function testCreateReleaseRaisesExceptionIfTokenIsMissing()
    {
        $this->gitlab->setPackageName('some/package');
        $this->expectException(Exception\MissingTokenException::class);
        $this->gitlab->createRelease('some/package 1.2.3', 'v1.2.3', 'the changelog');
    }
}
