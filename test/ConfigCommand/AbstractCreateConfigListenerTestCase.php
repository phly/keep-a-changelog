<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractCreateConfigListener;
use Phly\KeepAChangelog\ConfigCommand\CreateConfigEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

use function file_exists;
use function file_get_contents;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function unlink;

use const E_WARNING;

abstract class AbstractCreateConfigListenerTestCase extends TestCase
{
    /** @var null|string */
    public $existingConfigFile;

    /** @var null|string */
    public $tempConfigFile;

    /**
     * This method should set $tempConfigFile
     */
    abstract public function getListener() : AbstractCreateConfigListener;

    /**
     * This method should set $existingConfigFile
     */
    abstract public function getListenerWithExistingFile() : AbstractCreateConfigListener;

    /**
     * This method should set $tempConfigFile
     */
    abstract public function getListenerToFailCreatingFile() : AbstractCreateConfigListener;

    abstract public function configureEventToCreate(ObjectProphecy $event) : void;

    abstract public function configureEventToSkipCreate(ObjectProphecy $event) : void;

    public function setUp()
    {
        $this->existingConfigFile = null;
        $this->tempConfigFile     = null;
    }

    public function tearDown()
    {
        $this->existingConfigFile = null;
        if ($this->tempConfigFile && file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
        }
        $this->tempConfigFile = null;
    }

    public function getEventProphecy() : ObjectProphecy
    {
        $voidReturn = function () {
        };

        $event = $this->prophesize(CreateConfigEvent::class);
        $event->fileExists(Argument::any())->will($voidReturn);
        $event->customChangelog()->will($voidReturn);
        $event->creationFailed(Argument::any())->will($voidReturn);
        $event->createdConfigFile(Argument::any())->will($voidReturn);

        return $event;
    }

    public function testReturnsEarlyIfEventIsNotAllowedToCreateConfig()
    {
        $listener = $this->getListener();

        $event = $this->getEventProphecy();
        $this->configureEventToSkipCreate($event);

        $this->assertNull($listener($event->reveal()));

        $event->fileExists(Argument::any())->shouldNotHaveBeenCalled();
        $event->customChangelog()->shouldNotHaveBeenCalled();
        $event->creationFailed(Argument::any())->shouldNotHaveBeenCalled();
        $event->createdConfigFile(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReturnsEarlyIfFileExists()
    {
        $listener = $this->getListenerWithExistingFile();

        $event = $this->getEventProphecy();
        $this->configureEventToCreate($event);
        $event->fileExists($this->existingConfigFile)->shouldBeCalled();

        $this->assertNull($listener($event->reveal()));

        $event->customChangelog()->shouldNotHaveBeenCalled();
        $event->creationFailed(Argument::any())->shouldNotHaveBeenCalled();
        $event->createdConfigFile(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function changelogFiles() : iterable
    {
        yield 'null' => [null, 'CHANGELOG.md'];
        yield 'custom' => ['changelog.txt', 'changelog.txt'];
    }

    /**
     * @dataProvider changelogFiles
     */
    public function testNotifiesOfCreationFailure(?string $changelog)
    {
        $listener = $this->getListenerToFailCreatingFile();

        $event = $this->getEventProphecy();
        $this->configureEventToCreate($event);
        $event->customChangelog()->willReturn($changelog);
        $event->creationFailed($this->tempConfigFile)->shouldBeCalled();

        set_error_handler(function ($errno, $errmsg) {
            return true;
        }, E_WARNING);
        $this->assertNull($listener($event->reveal()));
        restore_error_handler();

        $event->fileExists(Argument::any())->shouldNotHaveBeenCalled();
        $event->createdConfigFile(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @dataProvider changelogFiles
     */
    public function testNotifiesOfConfigCreation(?string $changelog, string $expectedChangelog)
    {
        $listener = $this->getListener();

        $event = $this->getEventProphecy();
        $this->configureEventToCreate($event);
        $event->customChangelog()->willReturn($changelog);
        $event->createdConfigFile($this->tempConfigFile)->shouldBeCalled();

        $this->assertNull($listener($event->reveal()));

        $event->fileExists(Argument::any())->shouldNotHaveBeenCalled();
        $event->creationFailed(Argument::any())->shouldNotHaveBeenCalled();

        $contents = file_get_contents($this->tempConfigFile);
        $this->assertRegExp(sprintf('/^changelog_file = %s$/m', $expectedChangelog), $contents);
    }
}
