<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Provider;

use Github\Api\Issue;
use Github\Api\Issue\Milestones;
use Github\Client as GitHubClient;
use Phly\KeepAChangelog\Provider\Exception;
use Phly\KeepAChangelog\Provider\GitHub;
use Phly\KeepAChangelog\Provider\Milestone;
use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function array_shift;
use function file_get_contents;
use function json_decode;

class GitHubTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
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

    public function invalidUrls(): iterable
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

    public function invalidPackageNames(): iterable
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

    public function packageAndPatchLinks(): iterable
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

    public function packageAndIssueLinks(): iterable
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

    public function testProviderIsMilestoneAware(): void
    {
        $this->assertInstanceOf(MilestoneAwareProviderInterface::class, $this->github);
    }

    public function testListMilestonesRaisesExceptionIfPackageIsMissing(): void
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->github->listMilestones();
    }

    public function testListMilestonesReturnsArrayOfMilestoneInstances(): void
    {
        $milestonesFromApi = json_decode(
            file_get_contents(__DIR__ . '/../_files/milestones.json'),
            true
        );

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->all('phly', 'keep-a-changelog', ['state' => 'open'])
            ->willReturn($milestonesFromApi)
            ->shouldBeCalled();

        $issueApi = $this->prophesize(Issue::class);
        $issueApi->milestones()->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $client = $this->prophesize(GitHubClient::class);
        $client->api('issue')->will([$issueApi, 'reveal'])->shouldBeCalled();

        $this->github->client = $client->reveal();
        $this->github->setPackageName('phly/keep-a-changelog');

        $milestones = $this->github->listMilestones();

        $this->assertIsArray($milestones);
        $this->assertCount(1, $milestones);
        $milestone = array_shift($milestones);
        $this->assertInstanceOf(Milestone::class, $milestone);
        $this->assertSame(1, $milestone->id());
        $this->assertSame('v1.0', $milestone->title());
        $this->assertSame('Tracking milestone for version 1.0', $milestone->description());
    }

    public function testCreateMilestoneRaisesExceptionIfPackageIsMissing(): void
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->github->createMilestone('17.0.0');
    }

    public function testCreateMilestoneRaisesExceptionIfTokenIsMissing(): void
    {
        $this->github->setPackageName('phly/keep-a-changelog');
        $this->expectException(Exception\MissingTokenException::class);
        $this->github->createMilestone('17.0.0');
    }

    public function testCreateMilestoneReturnsCreatedMilestone(): void
    {
        $milestonesFromApi = json_decode(
            file_get_contents(__DIR__ . '/../_files/milestones.json'),
            true
        );
        $milestoneFromApi  = array_shift($milestonesFromApi);

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->create(
                'phly',
                'keep-a-changelog',
                [
                    'title'       => '17.0.0',
                    'description' => 'A long time in the future',
                ]
            )
            ->willReturn($milestoneFromApi)
            ->shouldBeCalled();

        $issueApi = $this->prophesize(Issue::class);
        $issueApi->milestones()->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $client = $this->prophesize(GitHubClient::class);
        $client->api('issue')->will([$issueApi, 'reveal'])->shouldBeCalled();

        $this->github->client = $client->reveal();
        $this->github->setPackageName('phly/keep-a-changelog');
        $this->github->setToken('not-really-a-token');

        $milestone = $this->github->createMilestone('17.0.0', 'A long time in the future');

        // ID will not match the milestone we are "creating"; main thing is
        // to validate that we cast what's returned from the API to a Milestone
        // instance.
        $this->assertInstanceOf(Milestone::class, $milestone);
        $this->assertSame(1, $milestone->id());
        $this->assertSame('17.0.0', $milestone->title());
        $this->assertSame('A long time in the future', $milestone->description());
    }

    public function testCloseMilestoneRaisesExceptionIfPackageIsMissing(): void
    {
        $this->expectException(Exception\MissingPackageNameException::class);
        $this->github->closeMilestone(1);
    }

    public function testCloseMilestoneRaisesExceptionIfTokenIsMissing(): void
    {
        $this->github->setPackageName('phly/keep-a-changelog');
        $this->expectException(Exception\MissingTokenException::class);
        $this->github->closeMilestone(1);
    }

    public function testCloseMilestoneReturnsFalseIfReturnedMilestoneStateIsNotClosed(): void
    {
        $milestonesFromApi = json_decode(
            file_get_contents(__DIR__ . '/../_files/milestones.json'),
            true
        );
        $milestoneFromApi  = array_shift($milestonesFromApi);

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->update('phly', 'keep-a-changelog', 1, ['state' => 'closed'])
            ->willReturn($milestoneFromApi)
            ->shouldBeCalled();

        $issueApi = $this->prophesize(Issue::class);
        $issueApi->milestones()->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $client = $this->prophesize(GitHubClient::class);
        $client->api('issue')->will([$issueApi, 'reveal'])->shouldBeCalled();

        $this->github->client = $client->reveal();
        $this->github->setPackageName('phly/keep-a-changelog');
        $this->github->setToken('not-really-a-token');

        $this->assertFalse(
            $this->github->closeMilestone(1)
        );
    }

    public function testCloseMilestoneReturnsTrueIfReturnedMilestoneStateIsClosed(): void
    {
        $milestonesFromApi         = json_decode(
            file_get_contents(__DIR__ . '/../_files/milestones.json'),
            true
        );
        $milestoneFromApi          = array_shift($milestonesFromApi);
        $milestoneFromApi['state'] = 'closed';

        $milestonesApi = $this->prophesize(Milestones::class);
        $milestonesApi
            ->update('phly', 'keep-a-changelog', 1, ['state' => 'closed'])
            ->willReturn($milestoneFromApi)
            ->shouldBeCalled();

        $issueApi = $this->prophesize(Issue::class);
        $issueApi->milestones()->will([$milestonesApi, 'reveal'])->shouldBeCalled();

        $client = $this->prophesize(GitHubClient::class);
        $client->api('issue')->will([$issueApi, 'reveal'])->shouldBeCalled();

        $this->github->client = $client->reveal();
        $this->github->setPackageName('phly/keep-a-changelog');
        $this->github->setToken('not-really-a-token');

        $this->assertTrue(
            $this->github->closeMilestone(1)
        );
    }
}
