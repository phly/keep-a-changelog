<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractEditConfigListener;
use Phly\KeepAChangelog\ConfigCommand\EditGlobalConfigListener;
use Prophecy\Prophecy\ObjectProphecy;

class EditGlobalConfigListenerTest extends AbstractEditConfigListenerTestCase
{
    public function getListener() : AbstractEditConfigListener
    {
        $listener             = new EditGlobalConfigListener();
        $listener->configRoot = __DIR__ . '/../_files/config';
        return $listener;
    }

    public function getListenerWithFileNotFound() : AbstractEditConfigListener
    {
        $listener             = new EditGlobalConfigListener();
        $listener->configRoot = __DIR__;
        return $listener;
    }

    public function configureEventToEdit(ObjectProphecy $event) : void
    {
        $event->editGlobal()->willReturn(true);
    }

    public function configureEventToSkipEdit(ObjectProphecy $event) : void
    {
        $event->editGlobal()->willReturn(false);
    }
}
