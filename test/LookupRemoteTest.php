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
use Prophecy\Argument;
use ReflectionMethod;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class LookupRemoteTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
    }

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
        ($this->getMethod())(
            $this->input->reveal(),
            $this->output->reveal(),
            $provider,
            'phly/keep-a-changelog',
            []
        );
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

            public function withDomainName(string $domain) : Provider\ProviderNameProvider
            {
                // no-op
            }
        };

        $remote = ($this->getMethod())(
            $this->input->reveal(),
            $this->output->reveal(),
            $provider,
            'phly/keep-a-changelog',
            [
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(fetch)",
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(push)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(fetch)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(push)",
                "custom\tgit@github.com:phly/keep-a-changelog.git\t(fetch)",
                "custom\tgit@github.com:phly/keep-a-changelog.git\t(push)",
            ]
        );

        $this->assertNull($remote);
    }

    public function testLookupRemoteReturnsRemoteWhenExactlyOneMatches()
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

            public function withDomainName(string $domain) : Provider\ProviderNameProvider
            {
                // no-op
            }
        };

        $remote = ($this->getMethod())(
            $this->input->reveal(),
            $this->output->reveal(),
            $provider,
            'phly/keep-a-changelog',
            [
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(fetch)",
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(push)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(fetch)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(push)",
                "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(fetch)",
                "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(push)",
            ]
        );

        $this->assertSame('custom', $remote);
    }

    public function testLookupRemotePromptsUserWhenMultipleMatchesFound()
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

            public function withDomainName(string $domain) : Provider\ProviderNameProvider
            {
                // no-op
            }
        };

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$this->input, 'reveal']),
                Argument::that([$this->output, 'reveal']),
                Argument::that(function ($question) {
                    TestCase::assertInstanceOf(ChoiceQuestion::class, $question);
                    TestCase::assertRegExp('/more than one valid remote/i', $question->getQuestion());
                    TestCase::assertEquals([
                        'custom',
                        'customHttps',
                        'abort' => 'Abort release',
                    ], $question->getChoices());

                    return $question;
                })
            )
            ->willReturn('customHttps');

        $helperSet = $this->prophesize(HelperSet::class);
        $helperSet->get('question')->will([$questionHelper, 'reveal']);

        $command = new ReleaseCommand();
        $command->setHelperSet($helperSet->reveal());

        $r = new ReflectionMethod($command, 'lookupRemote');
        $r->setAccessible(true);
        $method = function (...$arguments) use ($r, $command) {
            return $r->invokeArgs($command, $arguments);
        };

        $remote = $method(
            $this->input->reveal(),
            $this->output->reveal(),
            $provider,
            'phly/keep-a-changelog',
            [
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(fetch)",
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(push)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(fetch)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(push)",
                "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(fetch)",
                "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(push)",
                "customHttps\thttps://custom.develop:phly/keep-a-changelog.git\t(fetch)",
                "customHttps\thttps://custom.develop:phly/keep-a-changelog.git\t(push)",
            ]
        );

        $this->assertSame('customHttps', $remote);
    }

    public function testCanAbortWhenMultipleMatchesFound()
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

            public function withDomainName(string $domain) : Provider\ProviderNameProvider
            {
                // no-op
            }
        };

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$this->input, 'reveal']),
                Argument::that([$this->output, 'reveal']),
                Argument::that(function ($question) {
                    TestCase::assertInstanceOf(ChoiceQuestion::class, $question);
                    TestCase::assertRegExp('/more than one valid remote/i', $question->getQuestion());
                    TestCase::assertEquals([
                        'custom',
                        'customHttps',
                        'abort' => 'Abort release',
                    ], $question->getChoices());

                    return $question;
                })
            )
            ->willReturn('Abort release');

        $helperSet = $this->prophesize(HelperSet::class);
        $helperSet->get('question')->will([$questionHelper, 'reveal']);

        $command = new ReleaseCommand();
        $command->setHelperSet($helperSet->reveal());

        $r = new ReflectionMethod($command, 'lookupRemote');
        $r->setAccessible(true);
        $method = function (...$arguments) use ($r, $command) {
            return $r->invokeArgs($command, $arguments);
        };

        $remote = $method(
            $this->input->reveal(),
            $this->output->reveal(),
            $provider,
            'phly/keep-a-changelog',
            [
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(fetch)",
                "origin\tgit://github.com/phly/keep-a-changelog.git\t(push)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(fetch)",
                "upstream\thttps://github.com/phly/keep-a-changelog.git\t(push)",
                "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(fetch)",
                "custom\tgit@custom.develop:phly/keep-a-changelog.git\t(push)",
                "customHttps\thttps://custom.develop:phly/keep-a-changelog.git\t(fetch)",
                "customHttps\thttps://custom.develop:phly/keep-a-changelog.git\t(push)",
            ]
        );

        $this->assertNull($remote);
        $this->output->writeln(Argument::containingString('Aborted'))->shouldHaveBeenCalled();
    }
}
