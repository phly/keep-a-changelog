<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Release\DiscoverChangelogFileListener;
use Phly\KeepAChangelog\Release\ReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DiscoverChangelogFileListenerTest extends TestCase
{
    public function setUp()
    {
        $this->config = $this->prophesize(Config::class);
        $this->event  = $this->prophesize(ReleaseEvent::class);

        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function testDoesNothingIfConfiguredChangelogFileIsReadable()
    {
        $changelogFile = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $this->config->changelogFile()->willReturn($changelogFile);

        $listener = new DiscoverChangelogFileListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->changelogFileIsUnreadable(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testTellsEventChangelogFileIsUnreadableIfProvidedFileIsNotReadable()
    {
        $expected = realpath(__DIR__) . '/CHANGELOG.md';
        $this->config->changelogFile()->willReturn($expected);
        $this->event->changelogFileIsUnreadable($expected)->shouldBeCalled();

        $listener = new DiscoverChangelogFileListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
