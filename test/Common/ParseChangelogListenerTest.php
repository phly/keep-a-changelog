<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogAwareEventInterface;
use Phly\KeepAChangelog\Common\ParseChangelogListener;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Exception\ExceptionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function realpath;

class ParseChangelogListenerTest extends TestCase
{
    private Config&MockObject $config;
    private ChangelogAwareEventInterface&MockObject $event;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->event  = $this->createMock(ChangelogAwareEventInterface::class);
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testListenerUpdatesChangelogWhenChangelogFileParsed()
    {
        $changelogFile = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $listener      = new ParseChangelogListener();

        $this->config->expects($this->once())->method('changelogFile')->willReturn($changelogFile);
        $this->event->expects($this->once())->method('version')->willReturn('1.1.0');
        $this->event->expects($this->once())->method('updateChangelog');

        $this->assertNull($listener($this->event));
    }

    public function testListenerNotifiesEventOfParsingErrors()
    {
        $changelogFile = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $listener      = new ParseChangelogListener();

        $this->config->expects($this->once())->method('changelogFile')->willReturn($changelogFile);
        $this->event->expects($this->once())->method('version')->willReturn('1.0.1');
        $this->event
            ->expects($this->once())
            ->method('errorParsingChangelog')
            ->with($changelogFile, $this->isInstanceOf(ExceptionInterface::class));

        $this->assertNull($listener($this->event));
    }
}
