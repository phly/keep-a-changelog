<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\IsChangelogReadableListener;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function realpath;

class IsChangelogReadableListenerTest extends TestCase
{
    private Config&MockObject $config;
    private ReleaseEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->event  = $this->createMock(ReleaseEvent::class);

        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testDoesNothingIfConfiguredChangelogFileIsReadable()
    {
        $changelogFile = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $this->config->expects($this->once())->method('changelogFile')->willReturn($changelogFile);
        $this->event->expects($this->never())->method('changelogFileIsUnreadable');

        $listener = new IsChangelogReadableListener();

        $this->assertNull($listener($this->event));
    }

    public function testTellsEventChangelogFileIsUnreadableIfProvidedFileIsNotReadable()
    {
        $changelogFile = realpath(__DIR__) . '/CHANGELOG.md';
        $this->config->expects($this->once())->method('changelogFile')->willReturn($changelogFile);
        $this->event->expects($this->once())->method('changelogFileIsUnreadable');

        $listener = new IsChangelogReadableListener();

        $this->assertNull($listener($this->event));
    }
}
