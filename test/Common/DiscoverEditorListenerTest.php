<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\DiscoverEditorListener;
use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use function getenv;
use function putenv;
use function sprintf;

class DiscoverEditorListenerTest extends TestCase
{
    /** @var null|string */
    private $editorEnvValue;

    /** @var array */
    private $serverSuperGlobal = [];

    protected function setUp(): void
    {
        $this->serverSuperGlobal = $_SERVER;
        $this->editorEnvValue    = getenv('EDITOR');
        $this->event             = $this->prophesize(EditorAwareEventInterface::class);
        $this->voidReturn        = function () {
        };
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
        $this->event->editor()->willReturn('vim');
        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->discoverEditor(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerNotifiesEventOfEditorFoundInEnv()
    {
        putenv('EDITOR=some-custom-editor');
        $this->event->editor()->will($this->voidReturn);
        $this->event->discoverEditor('some-custom-editor')->shouldBeCalled();

        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDefaultsToNotepadOnWindows()
    {
        putenv('EDITOR');
        $this->event->editor()->will($this->voidReturn);
        $_SERVER['OS'] = 'Windows 10';
        $this->event->discoverEditor('notepad')->shouldBeCalled();

        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDefaultsToViOnNonWindowsSystems()
    {
        putenv('EDITOR');
        $this->event->editor()->will($this->voidReturn);
        $_SERVER['OS'] = 'GNU/Linux';
        $this->event->discoverEditor('vi')->shouldBeCalled();

        $listener = new DiscoverEditorListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
