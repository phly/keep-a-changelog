<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\Editor;
use Phly\KeepAChangelog\ConfigCommand\AbstractEditConfigListener;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractEditConfigListenerTestCase extends TestCase
{
    use ProphecyTrait;

    abstract public function getListener(): AbstractEditConfigListener;

    abstract public function getListenerWithFileNotFound(): AbstractEditConfigListener;

    abstract public function configureEventToEdit(ObjectProphecy $event): void;

    abstract public function configureEventToSkipEdit(ObjectProphecy $event): void;

    protected function setUp(): void
    {
        $this->voidReturn = function () {
        };
        $this->output     = $this->prophesize(OutputInterface::class);
    }

    public function getEventProphecy(): ObjectProphecy
    {
        $event = $this->prophesize(EditConfigEvent::class);

        $event->output()->will([$this->output, 'reveal']);
        $event->editor()->willReturn('vim');
        $event->configFileNotFound(Argument::any())->will($this->voidReturn);
        $event->editFailed(Argument::any())->will($this->voidReturn);
        $event->editComplete(Argument::any())->will($this->voidReturn);

        return $event;
    }

    public function testListenerReturnsEarlyIfEventNotConfiguredToEdit()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToSkipEdit($event);

        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldNotHaveBeenCalled();
        $event->editFailed(Argument::any())->shouldNotHaveBeenCalled();
        $event->editComplete(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerReturnsEarlyIfConfigFileNotFound()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToEdit($event);

        $listener = $this->getListenerWithFileNotFound();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldHaveBeenCalled();
        $event->editFailed(Argument::any())->shouldNotHaveBeenCalled();
        $event->editComplete(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerReturnsEarlyIfEditFailed()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToEdit($event);
        $event->editor()->willReturn('vim');

        $editor = $this->prophesize(Editor::class);
        $editor
            ->spawnEditor(
                $this->output->reveal(),
                'vim',
                Argument::type('string')
            )
            ->willReturn(1);

        $listener         = $this->getListener();
        $listener->editor = $editor->reveal();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldNotHaveBeenCalled();
        $event->editFailed(Argument::type('string'))->shouldHaveBeenCalled();
        $event->editComplete(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerNotifiesEventOfCompletion()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToEdit($event);
        $event->editor()->willReturn('vim');

        $editor = $this->prophesize(Editor::class);
        $editor
            ->spawnEditor(
                $this->output->reveal(),
                'vim',
                Argument::type('string')
            )
            ->willReturn(0);

        $listener         = $this->getListener();
        $listener->editor = $editor->reveal();

        $this->assertNull($listener($event->reveal()));

        $event->configFileNotFound(Argument::any())->shouldNotHaveBeenCalled();
        $event->editFailed(Argument::any())->shouldNotHaveBeenCalled();
        $event->editComplete(Argument::type('string'))->shouldHaveBeenCalled();
    }
}
