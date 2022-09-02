<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractShowConfigListener;
use Phly\KeepAChangelog\ConfigCommand\ShowLocalConfigListener;
use Prophecy\Prophecy\ObjectProphecy;

class ShowLocalConfigListenerTest extends AbstractShowConfigListenerTestCase
{
    /** @var string */
    protected $configType = 'local';

    public function getListener(): AbstractShowConfigListener
    {
        $listener             = new ShowLocalConfigListener();
        $listener->configRoot = __DIR__ . '/../_files/config/local';
        return $listener;
    }

    public function getListenerWithFileNotFound(): AbstractShowConfigListener
    {
        $listener             = new ShowLocalConfigListener();
        $listener->configRoot = __DIR__;
        return $listener;
    }

    public function configureEventToShow(ObjectProphecy $event): void
    {
        $event->showLocal()->willReturn(true);
        $event->showMerged()->willReturn(false);
    }

    public function configureEventToSkipShow(ObjectProphecy $event): void
    {
        $event->showLocal()->willReturn(false);
        $event->showMerged()->willReturn(true);
    }
}
