<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\EditChangelogLinksEvent;
use Phly\KeepAChangelog\Changelog\FindChangelogLinksListener;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FindChangelogLinksListenerTest extends TestCase
{
    private Config&MockObject $config;
    private EditChangelogLinksEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->event  = $this->createMock(EditChangelogLinksEvent::class);

        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testNotifesEventWhenNoLinksDiscovered()
    {
        $this->config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG.md');

        $this->event->expects($this->atLeastOnce())->method('noLinksDiscovered');
        $this->event->expects($this->never())->method('discoveredLinks');

        $listener = new FindChangelogLinksListener();
        $this->assertNull($listener($this->event));
    }

    public function testNotifesEventWhenLinksDiscovered()
    {
        $this->config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG-WITH-LINKS.md');

        $this->event->expects($this->never())->method('noLinksDiscovered');
        $this->event
            ->expects($this->once())
            ->method('discoveredLinks')
            ->with($this->callback(function ($links) {
                TestCase::assertInstanceOf(ChangelogEntry::class, $links);
                TestCase::assertSame(70, $links->index);
                TestCase::assertSame(3, $links->length);
                return true;
            }));

        $listener = new FindChangelogLinksListener();
        $this->assertNull($listener($this->event));
    }
}
