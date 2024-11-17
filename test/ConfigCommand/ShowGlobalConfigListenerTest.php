<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractShowConfigListener;
use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent; // phpcs:ignore
use Phly\KeepAChangelog\ConfigCommand\ShowGlobalConfigListener;
use PHPUnit\Framework\MockObject\MockObject;

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

    public function configureEventToShow(ShowConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('showGlobal')->willReturn(true);
        $event->expects($this->any())->method('showMerged')->willReturn(false);
    }

    public function configureEventToSkipShow(ShowConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('showGlobal')->willReturn(false);
        $event->expects($this->any())->method('showMerged')->willReturn(true);
    }
}
