<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractEditConfigListener;
use Phly\KeepAChangelog\ConfigCommand\EditLocalConfigListener;
use Prophecy\Prophecy\ObjectProphecy;

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

    public function configureEventToEdit(ObjectProphecy $event): void
    {
        $event->editLocal()->willReturn(true);
    }

    public function configureEventToSkipEdit(ObjectProphecy $event): void
    {
        $event->editLocal()->willReturn(false);
    }
}
