<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\ChangelogParser;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionEvent;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionListener;
use PHPUnit\Framework\TestCase;

class RemoveChangelogVersionListenerTest extends TestCase
{
    /** @var null|string */
    private $filename;

    public function setUp()
    {
        $this->original = __DIR__ . '/../_files/CHANGELOG.md';
        $this->filename = null;
        $this->entry    = new ChangelogEntry();
        $this->config   = $this->prophesize(Config::class);
        $this->event    = $this->prophesize(RemoveChangelogVersionEvent::class);
        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->changelogEntry()->willReturn($this->entry);
    }

    public function tearDown()
    {
        if ($this->filename) {
            if (file_exists($this->filename)) {
                unlink($this->filename);
            }
            $this->filename = null;
        }
    }

    protected function createChangelogFile() : string
    {
        $this->filename = $filename = tempnam(sys_get_temp_dir(), 'CAK');
        file_put_contents($filename, file_get_contents($this->original));

        $this->config->changelogFile()->willReturn($this->filename);

        return $filename;
    }

    public function testDoesNotChangeChangelogFileIfEntryIsOutOfBounds()
    {
        $filename = $this->createChangelogFile();
        $this->event->versionRemoved()->shouldBeCalled();
        $this->entry->index = 1000;
        $this->entry->length = 22;

        $listener = new RemoveChangelogVersionListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->assertFileEquals($this->original, $filename);
    }

    public function knownVersions() : iterable
    {
        yield '2.0.0' => ['2.0.0', 4, 22];
        yield '1.1.0' => ['1.1.0', 26, 22];
        yield '0.1.0' => ['0.1.0', 48, 21];
    }

    /**
     * @dataProvider knownVersions
     */
    public function testCanRemoveValidVersionFromChangelogFile(
        string $version,
        int $index,
        int $length
    ) {
        $filename = $this->createChangelogFile();
        $this->entry->index = $index;
        $this->entry->length = $length;
        $this->event->versionRemoved()->shouldBeCalled();

        $listener = new RemoveChangelogVersionListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->assertFileNotEquals($this->original, $filename);

        $versions = iterator_to_array((new ChangelogParser())->findAllVersions($filename));
        $this->assertNotContains($version, $versions);
    }
}
