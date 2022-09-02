<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractShowConfigListener;
use Phly\KeepAChangelog\ConfigCommand\ShowGlobalConfigListener;
use Prophecy\Prophecy\ObjectProphecy;

class ShowGlobalConfigListenerTest extends AbstractShowConfigListenerTestCase
{
    /** @var string */
    protected $configType = 'global';

    public function getListener(): AbstractShowConfigListener
    {
        $listener             = new ShowGlobalConfigListener();
        $listener->configRoot = __DIR__ . '/../_files/config';
        return $listener;
    }

    public function getListenerWithFileNotFound(): AbstractShowConfigListener
    {
        $listener             = new ShowGlobalConfigListener();
        $listener->configRoot = __DIR__;
        return $listener;
    }

    public function configureEventToShow(ObjectProphecy $event): void
    {
        $event->showGlobal()->willReturn(true);
        $event->showMerged()->willReturn(false);
    }

    public function configureEventToSkipShow(ObjectProphecy $event): void
    {
        $event->showGlobal()->willReturn(false);
        $event->showMerged()->willReturn(true);
    }
}
