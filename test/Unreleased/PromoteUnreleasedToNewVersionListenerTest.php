<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use Phly\KeepAChangelog\Unreleased\PromoteUnreleasedToNewVersionListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PromoteUnreleasedToNewVersionListenerTest extends TestCase
{
    public function testWritesChangelogEntry(): void
    {
        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn('changelog.txt');

        $entry           = new ChangelogEntry();
        $entry->contents = <<<'END'
## Unreleased

### Added

- Nothing.

### Changed

- Nothing.

### Removed

- Nothing.

### Deprecated

- Nothing.

### Fixed

- Nothing.
END;
        $entry->index    = 4;
        $entry->length   = 22;

        $event = $this->prophesize(PromoteEvent::class);
        $event->changelogEntry()->willReturn($entry)->shouldBeCalled();
        $event->newVersion()->willReturn('2.5.0')->shouldBeCalled();
        $event->releaseDate()->willReturn('2020-07-16')->shouldBeCalled();
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();
        $event->changelogReady()->shouldBeCalled();

        $editor = $this->prophesize(ChangelogEditor::class);
        $editor
            ->update(
                'changelog.txt',
                Argument::containingString('## 2.5.0 - 2020-07-16'),
                $entry
            )
            ->shouldBeCalled();

        $listener                  = new PromoteUnreleasedToNewVersionListener();
        $listener->changelogEditor = $editor->reveal();

        $this->assertNull($listener($event->reveal()));
    }
}
