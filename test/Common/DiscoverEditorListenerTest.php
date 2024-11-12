<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\DiscoverEditorListener;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function getenv;
use function putenv;
use function sprintf;

class DiscoverEditorListenerTest extends TestCase
{

    private ?string $editorEnvValue;
    private EditorAwareEventInterface&MockObject $event;
    private array $serverSuperGlobal = [];

    protected function setUp(): void
    {
        $this->serverSuperGlobal = $_SERVER;
        $this->editorEnvValue    = getenv('EDITOR');
        $this->event             = $this->createMock(EditorAwareEventInterface::class);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverSuperGlobal;
        $this->editorEnvValue
            ? putenv(sprintf('EDITOR=%s', $this->editorEnvValue))
            : putenv('EDITOR');
    }

    public function testListenerReturnsEarlyIfEventAlreadyComposesEditor()
    {
        $this->event->expects($this->once())->method('editor')->willReturn('vim');
        $this->event->expects($this->never())->method('discoverEditor');

        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event));
    }

    public function testListenerNotifiesEventOfEditorFoundInEnv()
    {
        putenv('EDITOR=some-custom-editor');
        $this->event->expects($this->once())->method('editor')->willReturn(null);
        $this->event->expects($this->once())->method('discoverEditor')->with('some-custom-editor');

        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event));
    }

    public function testListenerDefaultsToNotepadOnWindows()
    {
        putenv('EDITOR');
        $this->event->expects($this->once())->method('editor')->willReturn(null);
        $_SERVER['OS'] = 'Windows 10';
        $this->event->expects($this->once())->method('discoverEditor')->with('notepad');

        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event));
    }

    public function testListenerDefaultsToViOnNonWindowsSystems()
    {
        putenv('EDITOR');
        $this->event->expects($this->once())->method('editor')->willReturn(null);
        $_SERVER['OS'] = 'GNU/Linux';
        $this->event->expects($this->once())->method('discoverEditor')->with('vi');

        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event));
    }
}
