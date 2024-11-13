<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractCreateConfigListener;
use Phly\KeepAChangelog\ConfigCommand\CreateConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_get_contents;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function unlink;

use const E_WARNING;

abstract class AbstractCreateConfigListenerTestCase extends TestCase
{
    /** @var null|string */
    public $existingConfigFile;

    /** @var null|string */
    public $tempConfigFile;

    /**
     * This method should set $tempConfigFile
     */
    abstract public function getListener(): AbstractCreateConfigListener;

    /**
     * This method should set $existingConfigFile
     */
    abstract public function getListenerWithExistingFile(): AbstractCreateConfigListener;

    /**
     * This method should set $tempConfigFile
     */
    abstract public function getListenerToFailCreatingFile(): AbstractCreateConfigListener;

    abstract public function configureEventToCreate(CreateConfigEvent&MockObject $event): void;

    abstract public function configureEventToSkipCreate(CreateConfigEvent&MockObject $event): void;

    protected function setUp(): void
    {
        $this->existingConfigFile = null;
        $this->tempConfigFile     = null;
    }

    protected function tearDown(): void
    {
        $this->existingConfigFile = null;
        if ($this->tempConfigFile && file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
        }
        $this->tempConfigFile = null;
    }

    public function getEvent(): CreateConfigEvent&MockObject
    {
        /** @var CreateConfigEvent&MockObject $event */
        $event = $this->createMock(CreateConfigEvent::class);
        return $event;
    }

    public function testReturnsEarlyIfEventIsNotAllowedToCreateConfig()
    {
        $event    = $this->getEvent();
        $listener = $this->getListener();

        $this->configureEventToSkipCreate($event);
        $event->expects($this->never())->method('fileExists');
        $event->expects($this->never())->method('customChangelog');
        $event->expects($this->never())->method('creationFailed');
        $event->expects($this->never())->method('createdConfigFile');

        $this->assertNull($listener($event));
    }

    public function testReturnsEarlyIfFileExists()
    {
        $listener = $this->getListenerWithExistingFile();
        $event    = $this->getEvent();

        $this->configureEventToCreate($event);
        $event->expects($this->once())->method('fileExists')->with($this->existingConfigFile);
        $event->expects($this->never())->method('customChangelog');
        $event->expects($this->never())->method('creationFailed');
        $event->expects($this->never())->method('createdConfigFile');

        $this->assertNull($listener($event));
    }

    public function changelogFiles(): iterable
    {
        yield 'null' => [null, 'CHANGELOG.md'];
        yield 'custom' => ['changelog.txt', 'changelog.txt'];
    }

    /**
     * @dataProvider changelogFiles
     */
    public function testNotifiesOfCreationFailure(?string $changelog)
    {
        $listener = $this->getListenerToFailCreatingFile();
        $event    = $this->getEvent();

        $this->configureEventToCreate($event);
        $event->expects($this->never())->method('fileExists');
        $event->expects($this->once())->method('customChangelog')->willReturn($changelog);
        $event->expects($this->once())->method('creationFailed')->with($this->tempConfigFile);
        $event->expects($this->never())->method('createdConfigFile');

        set_error_handler(function ($errno, $errmsg) {
            return true;
        }, E_WARNING);
        $this->assertNull($listener($event));
        restore_error_handler();
    }

    /**
     * @dataProvider changelogFiles
     */
    public function testNotifiesOfConfigCreation(?string $changelog, string $expectedChangelog)
    {
        $listener = $this->getListener();

        $event = $this->getEvent();
        $this->configureEventToCreate($event);

        $event->expects($this->never())->method('fileExists');
        $event->expects($this->once())->method('customChangelog')->willReturn($changelog);
        $event->expects($this->never())->method('creationFailed');
        $event->expects($this->once())->method('createdConfigFile')->with($this->tempConfigFile);

        $this->assertNull($listener($event));

        $contents = file_get_contents($this->tempConfigFile);
        $this->assertMatchesRegularExpression(sprintf('/^changelog_file = %s$/m', $expectedChangelog), $contents);
    }
}
