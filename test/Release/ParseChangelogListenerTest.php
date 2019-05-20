<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Exception\ExceptionInterface;
use Phly\KeepAChangelog\Release\ParseChangelogListener;
use Phly\KeepAChangelog\Release\ReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ParseChangelogListenerTest extends TestCase
{
    public function setUp()
    {
        $this->config = $this->prophesize(Config::class);
        $this->event  = $this->prophesize(ReleaseEvent::class);
        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function testListenerSetsRawChangelogWhenChangelogFileParsed()
    {
        $changelogFile = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $expected = <<< 'EOC'
### Added

- Added a new feature.

### Changed

- Made some changes.

### Deprecated

- Nothing was deprecated.

### Removed

- Nothing was removed.

### Fixed

- Fixed some bugs.

EOC;
        $listener = new ParseChangelogListener();

        $this->config->changelogFile()->willReturn($changelogFile);
        $this->event->version()->willReturn('1.1.0');
        $this->event->setRawChangelog($expected)->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerNotifiesEventOfParsingErrors()
    {
        $changelogFile = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $listener = new ParseChangelogListener();

        $this->config->changelogFile()->willReturn($changelogFile);
        $this->event->version()->willReturn('1.0.1');
        $this->event
            ->errorParsingChangelog(
                $changelogFile,
                Argument::type(ExceptionInterface::class)
            )
            ->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }
}
