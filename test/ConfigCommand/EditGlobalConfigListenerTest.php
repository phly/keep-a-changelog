<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractEditConfigListener;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent; // phpcs:ignore
use Phly\KeepAChangelog\ConfigCommand\EditGlobalConfigListener;
use PHPUnit\Framework\MockObject\MockObject;

class EditGlobalConfigListenerTest extends AbstractEditConfigListenerTestCase
{
    public function getListener(): AbstractEditConfigListener
    {
        $listener             = new EditGlobalConfigListener();
        $listener->configRoot = __DIR__ . '/../_files/config';
        return $listener;
    }

    public function getListenerWithFileNotFound(): AbstractEditConfigListener
    {
        $listener             = new EditGlobalConfigListener();
        $listener->configRoot = __DIR__;
        return $listener;
    }

    public function configureEventToEdit(EditConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('editGlobal')->willReturn(true);
    }

    public function configureEventToSkipEdit(EditConfigEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('editGlobal')->willReturn(false);
    }
}
