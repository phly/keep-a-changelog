<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractEditConfigListener;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use Phly\KeepAChangelog\ConfigCommand\EditLocalConfigListener;
use PHPUnit\Framework\MockObject\MockObject;

class EditLocalConfigListenerTest extends AbstractEditConfigListenerTestCase
{
    public function getListener(): AbstractEditConfigListener
    {
        $listener             = new EditLocalConfigListener();
        $listener->configRoot = __DIR__ . '/../_files/config/local';
        return $listener;
    }

    public function getListenerWithFileNotFound(): AbstractEditConfigListener
    {
        $listener             = new EditLocalConfigListener();
        $listener->configRoot = __DIR__;
        return $listener;
    }

    public function configureEventToEdit(EditConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('editLocal')->willReturn(true);
    }

    public function configureEventToSkipEdit(EditConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('editLocal')->willReturn(false);
    }
}
