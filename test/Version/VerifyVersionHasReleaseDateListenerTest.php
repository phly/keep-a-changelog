<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\TagReleaseEvent;
use Phly\KeepAChangelog\Version\VerifyVersionHasReleaseDateListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyVersionHasReleaseDateListenerTest extends TestCase
{
    public function testDoesNothingIfChangelogHasAssociatedReleaseDate() : void
    {
        $version   = '1.2.3';
        $changelog = <<<'END'
## 1.2.3 - 2020-07-15

### Added

- Nothing.

### Changed

- Nothing.

### Removed

- Nothing.

### Deprecated

- Nothing.

### Fixed

- Something.
END;
        /** @var TagReleaseEvent|ObjectProphecy $event */
        $event = $this->prophesize(TagReleaseEvent::class);
        $event->changelog()->willReturn($changelog)->shouldBeCalled();
        $event->version()->willReturn($version)->shouldBeCalledTimes(1);
        $event->taggingFailed()->shouldNotBeCalled();
        $event->output()->shouldNotBeCalled();

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event->reveal()));
    }

    public function testNotifiesEventTaggingFailedIfChangelogDoesNotHaveReleaseDate() : void
    {
        $version   = '1.2.3';
        $changelog = <<<'END'
## 1.2.3 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Removed

- Nothing.

### Deprecated

- Nothing.

### Fixed

- Something.
END;

        /** @var OutputInterface|ObjectProphecy $event */
        $output = $this->prophesize(OutputInterface::class);
        $output
            ->writeln(Argument::containingString('Version 1.2.3 does not have a release date'))
            ->shouldBeCalled();
        $output
            ->writeln(Argument::containingString('version:ready'))
            ->shouldBeCalled();

        /** @var TagReleaseEvent|ObjectProphecy $event */
        $event = $this->prophesize(TagReleaseEvent::class);
        $event->changelog()->willReturn($changelog)->shouldBeCalled();
        $event->version()->willReturn($version)->shouldBeCalled();
        $event->taggingFailed()->shouldBeCalled();
        $event->output()->will([$output, 'reveal'])->shouldBeCalled();

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event->reveal()));
    }
}
