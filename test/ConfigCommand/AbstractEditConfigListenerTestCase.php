<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\Editor;
use Phly\KeepAChangelog\ConfigCommand\AbstractEditConfigListener;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractEditConfigListenerTestCase extends TestCase
{
    protected OutputInterface&MockObject $output;

    abstract public function getListener(): AbstractEditConfigListener;

    abstract public function getListenerWithFileNotFound(): AbstractEditConfigListener;

    abstract public function configureEventToEdit(EditConfigEvent&MockObject $event): void;

    abstract public function configureEventToSkipEdit(EditConfigEvent&MockObject $event): void;

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function getEvent(): EditConfigEvent&MockObject
    {
        /** @var EditConfigEvent&MockObject $event */
        $event = $this->createMock(EditConfigEvent::class);

        $event->expects($this->any())->method('output')->willReturn($this->output);
        $event->expects($this->any())->method('editor')->willReturn('vim');

        return $event;
    }

    public function testListenerReturnsEarlyIfEventNotConfiguredToEdit()
    {
        $event = $this->getEvent();
        $this->configureEventToSkipEdit($event);
        $event->expects($this->never())->method('configFileNotFound');
        $event->expects($this->never())->method('editFailed');
        $event->expects($this->never())->method('editComplete');

        $listener = $this->getListener();

        $this->assertNull($listener($event));
    }

    public function testListenerReturnsEarlyIfConfigFileNotFound()
    {
        $event = $this->getEvent();
        $this->configureEventToEdit($event);
        $event->expects($this->once())->method('configFileNotFound');
        $event->expects($this->never())->method('editFailed');
        $event->expects($this->never())->method('editComplete');

        $listener = $this->getListenerWithFileNotFound();

        $this->assertNull($listener($event));
    }

    public function testListenerReturnsEarlyIfEditFailed()
    {
        $event = $this->getEvent();
        $this->configureEventToEdit($event);
        $event->expects($this->never())->method('configFileNotFound');
        $event->expects($this->once())->method('editFailed')->with($this->isType('string'));
        $event->expects($this->never())->method('editComplete');

        $editor = $this->createMock(Editor::class);
        $editor
            ->expects($this->once())
            ->method('spawnEditor')
            ->with($this->output, 'vim', $this->isType('string'))
            ->willReturn(1);

        $listener         = $this->getListener();
        $listener->editor = $editor;

        $this->assertNull($listener($event));
    }

    public function testListenerNotifiesEventOfCompletion()
    {
        $event = $this->getEvent();
        $this->configureEventToEdit($event);
        $event->expects($this->never())->method('configFileNotFound');
        $event->expects($this->never())->method('editFailed');
        $event->expects($this->once())->method('editComplete')->with($this->isType('string'));

        $editor = $this->createMock(Editor::class);
        $editor
            ->expects($this->once())
            ->method('spawnEditor')
            ->with($this->output, 'vim', $this->isType('string'))
            ->willReturn(0);

        $listener         = $this->getListener();
        $listener->editor = $editor;

        $this->assertNull($listener($event));
    }
}
