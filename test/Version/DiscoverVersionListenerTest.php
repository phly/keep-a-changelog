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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DiscoverVersionListenerTest extends TestCase
{
    public function testReturnsEarlyWhenEventHasVersionComposed(): void
    {
        $event = $this->createMock(TagReleaseEvent::class);
        $event->expects($this->atLeastOnce())->method('version')->willReturn('1.2.3');
        $event->expects($this->never())->method('versionNotAccepted');
        $event->expects($this->never())->method('foundVersion');

        $listener = new DiscoverVersionListener();

        $this->assertNull($listener($event));
    }

    public function testReturnsEarlyWhenEventDoesNotHaveVersionComposedAndChangelogDoesNotHaveDatedEntries(): void
    {
        $config = $this->createMock(Config::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG-MULTIPLE-UNRELEASED.md');

        $event = $this->createMock(TagReleaseEvent::class);
        $event->expects($this->atLeastOnce())->method('version')->willReturn(null);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->never())->method('versionNotAccepted');
        $event->expects($this->never())->method('foundVersion');

        $listener = new DiscoverVersionListener();

        $this->assertNull($listener($event));
    }

    public function testNotifiesEventOfInvalidVersionSpecifiedWhenQuestionAnsweredIncorrectly(): void
    {
        $config = $this->createMock(Config::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG-WITH-RELEASED-VERSIONS.md');

        $input  = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $event = $this->createMock(TagReleaseEvent::class);
        $event->expects($this->atLeastOnce())->method('version')->willReturn(null);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->atLeastOnce())->method('input')->willReturn($input);
        $event->expects($this->atLeastOnce())->method('output')->willReturn($output);
        $event->expects($this->once())->method('versionNotAccepted');
        $event->expects($this->never())->method('foundVersion');

        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with($input, $output, $this->isInstanceOf(ConfirmationQuestion::class))
            ->willReturn(false);

        $listener                 = new DiscoverVersionListener();
        $listener->questionHelper = $questionHelper;

        $this->assertNull($listener($event));
    }

    public function testNotifiesEventOfVersionWhenUserProvidesIt(): void
    {
        $config = $this->createMock(Config::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG-WITH-RELEASED-VERSIONS.md');

        $input  = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $event = $this->createMock(TagReleaseEvent::class);
        $event->expects($this->atLeastOnce())->method('version')->willReturn(null);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->atLeastOnce())->method('input')->willReturn($input);
        $event->expects($this->atLeastOnce())->method('output')->willReturn($output);
        $event->expects($this->never())->method('versionNotAccepted');
        $event->expects($this->once())->method('foundVersion')->with('2.0.0');

        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with($input, $output, $this->isInstanceOf(ConfirmationQuestion::class))
            ->willReturn(true);

        $listener                 = new DiscoverVersionListener();
        $listener->questionHelper = $questionHelper;

        $this->assertNull($listener($event));
    }
}
