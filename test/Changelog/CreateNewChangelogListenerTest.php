<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\CreateNewChangelogEvent;
use Phly\KeepAChangelog\Changelog\CreateNewChangelogListener;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class CreateNewChangelogListenerTest extends TestCase
{
    /** @var null|string */
    private $tempFile;

    protected function setUp() : void
    {
        $voidReturn = function () {
        };

        $this->tempFile = null;
        $this->config   = $this->prophesize(Config::class);
        $this->event    = $this->prophesize(CreateNewChangelogEvent::class);
        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->version()->willReturn('1.0.0');
        $this->event->changelogExists(Argument::any())->will($voidReturn);
        $this->event->createdChangelog()->will($voidReturn);
    }

    protected function tearDown() : void
    {
        if ($this->tempFile) {
            if (file_exists($this->tempFile)) {
                unlink($this->tempFile);
            }
            $this->tempFile = null;
        }
    }

    public function testNotifesEventChangelogExistsIfFileExistsAndEventNotMarkedToOverwrite()
    {
        $changelog = __DIR__ . '/../_files/CHANGELOG.md';
        $this->config->changelogFile()->willReturn($changelog);

        $this->event->overwrite()->willReturn(false);

        $listener = new CreateNewChangelogListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->changelogExists($changelog)->shouldHaveBeenCalled();
        $this->event->version()->shouldNotHaveBeenCalled();
        $this->event->createdChangelog()->shouldNotHaveBeenCalled();
    }

    public function testNotifiesEventChangelogCreatedWhenFileDoesNotExistAndIsCreated()
    {
        $this->tempFile = $changelog = tempnam(sys_get_temp_dir(), 'CAK');
        unlink($changelog); // tempnam creates the file

        $this->config->changelogFile()->willReturn($changelog);
        $this->event->overwrite()->willReturn(false);

        $listener = new CreateNewChangelogListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->changelogExists($changelog)->shouldNotHaveBeenCalled();
        $this->event->version()->shouldHaveBeenCalled();
        $this->event->createdChangelog()->shouldHaveBeenCalled();

        $this->assertFileEquals(__DIR__ . '/../_files/CHANGELOG-INITIAL.md', $this->tempFile);
    }

    public function testNotifiesEventChangelogCreatedWhenFileDoesExistButAndIsOverwritten()
    {
        $this->tempFile = $changelog = tempnam(sys_get_temp_dir(), 'CAK');
        file_put_contents($changelog, file_get_contents(__DIR__ . '/../_files/CHANGELOG.md'));

        $this->config->changelogFile()->willReturn($changelog);
        $this->event->overwrite()->willReturn(true);

        $listener = new CreateNewChangelogListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->changelogExists($changelog)->shouldNotHaveBeenCalled();
        $this->event->version()->shouldHaveBeenCalled();
        $this->event->createdChangelog()->shouldHaveBeenCalled();

        $this->assertFileEquals(__DIR__ . '/../_files/CHANGELOG-INITIAL.md', $this->tempFile);
    }
}
