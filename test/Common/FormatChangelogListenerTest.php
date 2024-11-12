<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogAwareEventInterface;
use Phly\KeepAChangelog\Common\FormatChangelogListener;
use PHPUnit\Framework\TestCase;

class FormatChangelogListenerTest extends TestCase
{
    public function testListenerFormatsProvidedChangelogAndPushesItToTheEvent()
    {
        $event     = $this->createMock(ChangelogAwareEventInterface::class);
        $changelog = <<<'EOC'
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

        $event->expects($this->once())->method('changelog')->willReturn($changelog);
        $event->expects($this->once())->method('updateChangelog');

        $listener = new FormatChangelogListener();

        $this->assertNull($listener($event));
    }
}
