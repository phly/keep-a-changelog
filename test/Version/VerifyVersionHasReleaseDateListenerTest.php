<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use Phly\KeepAChangelog\Version\VerifyVersionHasReleaseDateListener;
use PHPUnit\Framework\TestCase;

class VerifyVersionHasReleaseDateListenerTest extends TestCase
{
    public function testDoesNothingIfChangelogHasAssociatedReleaseDate(): void
    {
        $config = $this->createMock(Config::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG.md');

        $event = $this->createMock(TagReleaseEvent::class);
        $event->expects($this->once())->method('config')->willReturn($config);
        $event->expects($this->once())->method('version')->willReturn('1.1.0');
        $event->expects($this->never())->method('changelogMissingDate');
        $event->expects($this->never())->method('output');

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event));
    }

    public function testNotifiesEventTaggingFailedIfChangelogDoesNotHaveReleaseDate(): void
    {
        $config = $this->createMock(Config::class);
        $event  = $this->createMock(TagReleaseEvent::class);

        $config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG.md');
        $event->expects($this->once())->method('config')->willReturn($config);
        $event->expects($this->once())->method('version')->willReturn('2.0.0');
        $event->expects($this->once())->method('changelogMissingDate');

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event));
    }
}
