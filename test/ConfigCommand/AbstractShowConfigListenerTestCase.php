<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractShowConfigListener;
use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractShowConfigListenerTestCase extends TestCase
{
    /**
     * Set to either "global" or "local"
     *
     * @var string
     */
    protected $configType;

    abstract public function getListener(): AbstractShowConfigListener;

    abstract public function getListenerWithFileNotFound(): AbstractShowConfigListener;

    abstract public function configureEventToShow(ShowConfigEvent&MockObject $event): void;

    abstract public function configureEventToSkipShow(ShowConfigEvent&MockObject $event): void;

    public function getEvent(): ShowConfigEvent&MockObject
    {
        /** @var ShowConfigEvent&MockObject $event */
        $event = $this->createMock(ShowConfigEvent::class);

        return $event;
    }

    public function testListenerReturnsEarlyIfEventNotConfiguredToShow()
    {
        $event = $this->getEvent();
        $this->configureEventToSkipShow($event);
        $event->expects($this->never())->method('configIsNotReadable');
        $event->expects($this->never())->method('displayConfig');

        $listener = $this->getListener();

        $this->assertNull($listener($event));
    }

    public function testListenerReturnsEarlyIfConfigFileNotReadable()
    {
        $event    = $this->getEvent();
        $listener = $this->getListenerWithFileNotFound();

        $this->configureEventToShow($event);
        $event
            ->expects($this->once())
            ->method('configIsNotReadable')
            ->with(
                $listener->getConfigFile(),
                $listener->getConfigType()
            );
        $event->expects($this->never())->method('displayConfig');

        $this->assertNull($listener($event));
    }

    public function testListenerTellsEventToDisplayConfig()
    {
        $event    = $this->getEvent();
        $listener = $this->getListener();

        $this->configureEventToShow($event);
        $event->expects($this->never())->method('configIsNotReadable');
        $event
            ->expects($this->once())
            ->method('displayConfig')
            ->with(
                $this->isType('string'),
                $this->configType,
                $listener->getConfigFile()
            );

        $this->assertNull($listener($event));
    }
}
