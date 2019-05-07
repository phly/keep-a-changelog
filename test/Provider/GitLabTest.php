<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use Phly\KeepAChangelog\Exception\InvalidPackageNameException;
use Phly\KeepAChangelog\Provider\GitLab;
use PHPUnit\Framework\TestCase;

class GitLabTest extends TestCase
{
    public function setUp()
    {
        $this->gitlab = new GitLab();
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
        $this->expectException(InvalidPackageNameException::class);
        $this->gitlab->generatePullRequestLink($package, 1);
    }

    public function validPackageNames() : iterable
    {
        // @codingStandardsIgnoreStart
        // @phpcs:disable
        yield 'typical'               => ['phly/keep-a-changelog', 42, 'https://gitlab.com/phly/keep-a-changelog/merge_requests/42'];
        yield 'typical-underscore'    => ['phly/keep_a_changelog', 42, 'https://gitlab.com/phly/keep_a_changelog/merge_requests/42'];
        yield 'subgroup'              => ['phly/cli/keep-a-changelog', 42, 'https://gitlab.com/phly/cli/keep-a-changelog/merge_requests/42'];
        // @phpcs:enable
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider validPackageNames
     */
    public function testGeneratePullRequestLinkCreatesExpectedPackageLinks(string $package, int $pr, string $expected)
    {
        $link = $this->gitlab->generatePullRequestLink($package, $pr);
        $this->assertSame($expected, $link);
    }
}
