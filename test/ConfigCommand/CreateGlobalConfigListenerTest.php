<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractCreateConfigListener;
use Phly\KeepAChangelog\ConfigCommand\CreateGlobalConfigListener;
use Prophecy\Prophecy\ObjectProphecy;

use function sprintf;
use function sys_get_temp_dir;

class CreateGlobalConfigListenerTest extends AbstractCreateConfigListenerTestCase
{
    public function getListener(): AbstractCreateConfigListener
    {
        $root                 = sys_get_temp_dir();
        $this->tempConfigFile = sprintf('%s/keep-a-changelog.ini', $root);

        $listener             = new CreateGlobalConfigListener();
        $listener->configRoot = $root;
        return $listener;
    }

    public function getListenerWithExistingFile(): AbstractCreateConfigListener
    {
        $root                     = __DIR__ . '/../_files/config';
        $this->existingConfigFile = sprintf('%s/keep-a-changelog.ini', $root);

        $listener             = new CreateGlobalConfigListener();
        $listener->configRoot = $root;
        return $listener;
    }

    public function getListenerToFailCreatingFile(): AbstractCreateConfigListener
    {
        $root                 = '/dev/null';
        $this->tempConfigFile = sprintf('%s/keep-a-changelog.ini', $root);

        $listener             = new CreateGlobalConfigListener();
        $listener->configRoot = $root;
        return $listener;
    }

    public function configureEventToCreate(ObjectProphecy $event): void
    {
        $event->createGlobal()->willReturn(true);
    }

    public function configureEventToSkipCreate(ObjectProphecy $event): void
    {
        $event->createGlobal()->willReturn(false);
    }
}
