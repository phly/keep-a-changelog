<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Provider;

use Gitlab\Api\Milestones;
use Gitlab\Client as GitLabClient;
use Phly\KeepAChangelog\Provider\Exception;
use Phly\KeepAChangelog\Provider\GitLab;
use Phly\KeepAChangelog\Provider\Milestone;
use PHPUnit\Framework\TestCase;

use function count;

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

    public function testListMilestonesRaisesExceptionIfPackageIsMissing()
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->gitlab->listMilestones();
    }

    public function testListMilestonesReturnsArrayOfMilestoneInstances()
    {
        $milestonesFromApi = [
            [
                'id'          => 1000,
                'title'       => '17.0.0',
                'description' => 'In the future',
                'state'       => 'active',
            ],
            [
                'id'          => 1001,
                'title'       => '17.0.1',
                'description' => 'In the future + 1',
                'state'       => 'active',
            ],
        ];

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->all('phly/keep-a-changelog', ['state' => 'active'])
            ->willReturn($milestonesFromApi)
            ->shouldBeCalled();

        $client = $this->prophesize(GitLabClient::class);
        $client->api('milestones')->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $this->gitlab->client = $client->reveal();
        $this->gitlab->setPackageName('phly/keep-a-changelog');

        $milestones = $this->gitlab->listMilestones();

        $this->assertIsArray($milestones);
        $this->assertGreaterThan(0, count($milestones));
        $this->assertContainsOnly(Milestone::class, $milestones);
    }

    public function testCreateMilestoneRaisesExceptionIfPackageIsMissing()
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->gitlab->createMilestone('17.0.0', 'In the future');
    }

    public function testCreateMilestoneRaisesExceptionIfTokenIsMissing()
    {
        $this->gitlab->setPackageName('phly/keep-a-changelog');
        $this->expectException(Exception\MissingTokenException::class);
        $this->gitlab->createMilestone('17.0.0', 'In the future');
    }

    public function testCreateMilestoneReturnsMilestoneInstance()
    {
        $milestoneFromApi = [
            'id'          => 1000,
            'title'       => '17.0.0',
            'description' => 'In the future',
            'state'       => 'active',
        ];

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->create('phly/keep-a-changelog', ['title' => '17.0.0', 'description' => 'In the future'])
            ->willReturn($milestoneFromApi)
            ->shouldBeCalled();

        $client = $this->prophesize(GitLabClient::class);
        $client->api('milestones')->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $this->gitlab->client = $client->reveal();
        $this->gitlab->setPackageName('phly/keep-a-changelog');
        $this->gitlab->setToken('not-really-a-token');

        $milestone = $this->gitlab->createMilestone('17.0.0', 'In the future');

        $this->assertInstanceOf(Milestone::class, $milestone);
        /** @var Milestone $milestone */
        $this->assertSame(1000, $milestone->id());
        $this->assertSame('17.0.0', $milestone->title());
        $this->assertSame('In the future', $milestone->description());
    }

    public function testCloseMilestoneRaisesExceptionIfPackageIsMissing()
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->gitlab->closeMilestone(1000);
    }

    public function testCloseMilestoneRaisesExceptionIfTokenIsMissing()
    {
        $this->gitlab->setPackageName('phly/keep-a-changelog');
        $this->expectException(Exception\MissingTokenException::class);
        $this->gitlab->closeMilestone(1000);
    }

    public function testCloseMilestoneReturnsFalseIfReturnedMilestoneIsActive()
    {
        $milestoneFromApi = [
            'id'          => 1000,
            'title'       => '17.0.0',
            'description' => 'In the future',
            'state'       => 'active',
        ];

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->update('phly/keep-a-changelog', 1000, ['state_event' => 'close'])
            ->willReturn($milestoneFromApi)
            ->shouldBeCalled();

        $client = $this->prophesize(GitLabClient::class);
        $client->api('milestones')->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $this->gitlab->client = $client->reveal();
        $this->gitlab->setPackageName('phly/keep-a-changelog');
        $this->gitlab->setToken('not-really-a-token');

        $this->assertFalse($this->gitlab->closeMilestone(1000));
    }

    public function testCloseMilestoneReturnsTrueIfReturnedMilestoneIsClosed()
    {
        $milestoneFromApi = [
            'id'          => 1000,
            'title'       => '17.0.0',
            'description' => 'In the future',
            'state'       => 'closed',
        ];

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->update('phly/keep-a-changelog', 1000, ['state_event' => 'close'])
            ->willReturn($milestoneFromApi)
            ->shouldBeCalled();

        $client = $this->prophesize(GitLabClient::class);
        $client->api('milestones')->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $this->gitlab->client = $client->reveal();
        $this->gitlab->setPackageName('phly/keep-a-changelog');
        $this->gitlab->setToken('not-really-a-token');

        $this->assertTrue($this->gitlab->closeMilestone(1000));
    }
}
