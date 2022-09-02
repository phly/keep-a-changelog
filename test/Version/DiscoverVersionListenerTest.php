<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\DiscoverVersionListener;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DiscoverVersionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testReturnsEarlyWhenEventHasVersionComposed(): void
    {
        $event = $this->prophesize(TagReleaseEvent::class);
        $event->version()->willReturn('1.2.3')->shouldBeCalled();

        $listener = new DiscoverVersionListener();

        $this->assertNull($listener($event->reveal()));

        $event->versionNotAccepted()->shouldNotHaveBeenCalled();
        $event->foundVersion(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReturnsEarlyWhenEventDoesNotHaveVersionComposedAndChangelogDoesNotHaveDatedEntries(): void
    {
        $config = $this->prophesize(Config::class);
        $config
            ->changelogFile()
            ->willReturn(__DIR__ . '/../_files/CHANGELOG-MULTIPLE-UNRELEASED.md')
            ->shouldBeCalled();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->version()->willReturn(null)->shouldBeCalled();
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();

        $listener = new DiscoverVersionListener();

        $this->assertNull($listener($event->reveal()));

        $event->versionNotAccepted()->shouldNotHaveBeenCalled();
        $event->foundVersion(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventOfInvalidVersionSpecifiedWhenQuestionAnsweredIncorrectly(): void
    {
        $config = $this->prophesize(Config::class);
        $config
            ->changelogFile()
            ->willReturn(__DIR__ . '/../_files/CHANGELOG-WITH-RELEASED-VERSIONS.md')
            ->shouldBeCalled();

        $input  = $this->prophesize(InputInterface::class)->reveal();
        $output = $this->prophesize(OutputInterface::class)->reveal();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->version()->willReturn(null)->shouldBeCalled();
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();
        $event->input()->willReturn($input)->shouldBeCalled();
        $event->output()->willReturn($output)->shouldBeCalled();

        $event->versionNotAccepted()->shouldBeCalled();
        $event->foundVersion(Argument::any())->shouldNotBeCalled();

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask($input, $output, Argument::type(ConfirmationQuestion::class))
            ->willReturn(false)
            ->shouldBeCalled();

        $listener                 = new DiscoverVersionListener();
        $listener->questionHelper = $questionHelper->reveal();

        $this->assertNull($listener($event->reveal()));
    }

    public function testNotifiesEventOfVersionWhenUserProvidesIt(): void
    {
        $config = $this->prophesize(Config::class);
        $config
            ->changelogFile()
            ->willReturn(__DIR__ . '/../_files/CHANGELOG-WITH-RELEASED-VERSIONS.md')
            ->shouldBeCalled();

        $input  = $this->prophesize(InputInterface::class)->reveal();
        $output = $this->prophesize(OutputInterface::class)->reveal();

        $event = $this->prophesize(TagReleaseEvent::class);
        $event->version()->willReturn(null)->shouldBeCalled();
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();
        $event->input()->willReturn($input)->shouldBeCalled();
        $event->output()->willReturn($output)->shouldBeCalled();

        $event->versionNotAccepted()->shouldNotBeCalled();
        $event->foundVersion('2.0.0')->shouldBeCalled();

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask($input, $output, Argument::type(ConfirmationQuestion::class))
            ->willReturn(true)
            ->shouldBeCalled();

        $listener                 = new DiscoverVersionListener();
        $listener->questionHelper = $questionHelper->reveal();

        $this->assertNull($listener($event->reveal()));
    }
}
