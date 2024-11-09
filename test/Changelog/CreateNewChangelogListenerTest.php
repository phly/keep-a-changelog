<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\CreateNewChangelogEvent;
use Phly\KeepAChangelog\Changelog\CreateNewChangelogListener;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class CreateNewChangelogListenerTest extends TestCase
{
    private Config&MockObject $config;
    private CreateNewChangelogEvent&MockObject $event;

    /** @var null|string */
    private $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = null;
        $this->config   = $this->createMock(Config::class);
        $this->event    = $this->createMock(CreateNewChangelogEvent::class);
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    protected function tearDown(): void
    {
        if ($this->tempFile) {
            if (file_exists($this->tempFile)) {
                unlink($this->tempFile);
            }
            $this->tempFile = null;
        }
    }

    public function testNotifesEventChangelogExistsIfFileExistsAndEventNotMarkedToOverwrite()
    {
        $changelog = __DIR__ . '/../_files/CHANGELOG.md';
        $this->config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn($changelog);
        $this->event
            ->expects($this->atLeastOnce())
            ->method('overwrite')
            ->willReturn(false);

        $listener = new CreateNewChangelogListener();

        $this->event->expects($this->atLeastOnce())->method('changelogExists')->with($changelog);
        $this->event->expects($this->never())->method('version');
        $this->event->expects($this->never())->method('createdChangelog');
        $this->assertNull($listener($this->event));
    }

    public function testNotifiesEventChangelogCreatedWhenFileDoesNotExistAndIsCreated()
    {
        $this->tempFile = $changelog = tempnam(sys_get_temp_dir(), 'CAK');
        unlink($changelog); // tempnam creates the file

        $this->config->expects($this->atLeastOnce())->method('changelogFile')->willReturn($changelog);
        $this->event->expects($this->atLeastOnce())->method('overwrite')->willReturn(false);

        $listener = new CreateNewChangelogListener();

        $this->event->expects($this->never())->method('changelogExists');
        $this->event->expects($this->atLeastOnce())->method('version')->willReturn('1.0.0');
        $this->event->expects($this->once())->method('createdChangelog');

        $this->assertNull($listener($this->event));
        $this->assertFileEquals(__DIR__ . '/../_files/CHANGELOG-INITIAL.md', $this->tempFile);
    }

    public function testNotifiesEventChangelogCreatedWhenFileDoesExistButAndIsOverwritten()
    {
        $this->tempFile = $changelog = tempnam(sys_get_temp_dir(), 'CAK');
        file_put_contents($changelog, file_get_contents(__DIR__ . '/../_files/CHANGELOG.md'));

        $this->config->expects($this->atLeastOnce())->method('changelogFile')->willReturn($changelog);
        $this->event->expects($this->atLeastOnce())->method('overwrite')->willReturn(true);

        $listener = new CreateNewChangelogListener();

        $this->event->expects($this->never())->method('changelogExists');
        $this->event->expects($this->atLeastOnce())->method('version')->willReturn('1.0.0');
        $this->event->expects($this->once())->method('createdChangelog');

        $this->assertNull($listener($this->event));
        $this->assertFileEquals(__DIR__ . '/../_files/CHANGELOG-INITIAL.md', $this->tempFile);
    }
}
