<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractShowConfigListener;
use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

abstract class AbstractShowConfigListenerTestCase extends TestCase
{
    /**
     * Set to either "global" or "local"
     *
     * @var string
     */
    protected $configType;

    abstract public function getListener() : AbstractShowConfigListener;

    abstract public function getListenerWithFileNotFound() : AbstractShowConfigListener;

    abstract public function configureEventToShow(ObjectProphecy $event) : void;

    abstract public function configureEventToSkipShow(ObjectProphecy $event) : void;

    protected function setUp() : void
    {
        $this->voidReturn = function () {
        };
    }

    public function getEventProphecy() : ObjectProphecy
    {
        $event = $this->prophesize(ShowConfigEvent::class);

        $event
            ->configIsNotReadable(Argument::any(), Argument::any())
            ->will($this->voidReturn);
        $event
            ->displayConfig(Argument::any(), Argument::any(), Argument::any())
            ->will($this->voidReturn);

        return $event;
    }

    public function testListenerReturnsEarlyIfEventNotConfiguredToShow()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToSkipShow($event);

        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));

        $event->configIsNotReadable(Argument::any())->shouldNotHaveBeenCalled();
        $event->displayConfig(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerReturnsEarlyIfConfigFileNotReadable()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToShow($event);

        $listener = $this->getListenerWithFileNotFound();

        $this->assertNull($listener($event->reveal()));

        $event
            ->configIsNotReadable(
                $listener->getConfigFile(),
                $listener->getConfigType()
            )
            ->shouldHaveBeenCalled();
        $event->displayConfig(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testListenerTellsEventToDisplayConfig()
    {
        $event = $this->getEventProphecy();
        $this->configureEventToShow($event);

        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));

        $event->configIsNotReadable(Argument::any())->shouldNotHaveBeenCalled();
        $event
            ->displayConfig(
                Argument::type('string'),
                $this->configType,
                $listener->getConfigFile()
            )
            ->shouldHaveBeenCalled();
    }
}
