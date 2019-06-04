<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractRemoveConfigListener;
use Phly\KeepAChangelog\ConfigCommand\RemoveLocalConfigListener;
use Prophecy\Prophecy\ObjectProphecy;

use function file_exists;
use function sprintf;
use function sys_get_temp_dir;
use function touch;
use function unlink;

class RemoveLocalConfigListenerTest extends AbstractRemoveConfigListenerTestCase
{
    /** @var null|string */
    private $tempFile;

    public function getListener() : AbstractRemoveConfigListener
    {
        $configRoot     = sys_get_temp_dir();
        $this->tempFile = sprintf('%s/.keep-a-changelog.ini', $configRoot);
        touch($this->tempFile);

        $listener             = new RemoveLocalConfigListener();
        $listener->configRoot = $configRoot;

        return $listener;
    }

    public function getListenerWithFileNotFound() : AbstractRemoveConfigListener
    {
        $configRoot     = sys_get_temp_dir();
        $this->tempFile = sprintf('%s/.keep-a-changelog.ini', $configRoot);

        $listener             = new RemoveLocalConfigListener();
        $listener->configRoot = $configRoot;

        return $listener;
    }

    public function getListenerWithUnlinkableFile() : AbstractRemoveConfigListener
    {
        $configRoot     = sys_get_temp_dir();
        $this->tempFile = sprintf('%s/.keep-a-changelog.ini', $configRoot);
        touch($this->tempFile);

        $unlink = function (string $filename) : bool {
            return false;
        };

        $listener             = new RemoveLocalConfigListener();
        $listener->configRoot = $configRoot;
        $listener->unlink     = $unlink;

        return $listener;
    }

    public function configureEventToRemove(ObjectProphecy $event) : void
    {
        $event->removeLocal()->willReturn(true);
    }

    public function configureEventToSkipRemove(ObjectProphecy $event) : void
    {
        $event->removeLocal()->willReturn(false);
    }

    public function setUp()
    {
        $this->tempFile = null;
        parent::setUp();
    }

    public function tearDown()
    {
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
        $this->tempFile = null;
    }
}
