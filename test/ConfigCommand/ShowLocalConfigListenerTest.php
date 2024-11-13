<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractShowConfigListener;
use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use Phly\KeepAChangelog\ConfigCommand\ShowLocalConfigListener;
use PHPUnit\Framework\MockObject\MockObject;

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

    public function configureEventToShow(ShowConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('showLocal')->willReturn(true);
        $event->expects($this->any())->method('showMerged')->willReturn(false);
    }

    public function configureEventToSkipShow(ShowConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('showLocal')->willReturn(false);
        $event->expects($this->any())->method('showMerged')->willReturn(true);
    }
}
