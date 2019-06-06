<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionEvent;
use Phly\KeepAChangelog\Version\RemoveChangelogVersionListener;
use PHPUnit\Framework\TestCase;

class RemoveChangelogVersionListenerTest extends TestCase
{
    public function setUp()
    {
        $this->entry  = new ChangelogEntry();
        $this->config = $this->prophesize(Config::class);
        $this->editor = $this->prophesize(ChangelogEditor::class);
        $this->event  = $this->prophesize(RemoveChangelogVersionEvent::class);

        $this->config->changelogFile()->willReturn('changelog.txt');

        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->changelogEntry()->willReturn($this->entry);
        $this->event->versionRemoved()->will(function () {
        });

        $this->listener                  = new RemoveChangelogVersionListener();
        $this->listener->changelogEditor = $this->editor->reveal();
    }

    public function testUpdatesChangelogWithEmptyContentsForEntry()
    {
        $this->editor
            ->update(
                'changelog.txt',
                '',
                $this->entry
            )
            ->will(function () {
            })
            ->shouldBeCalled();

        $this->assertNull(($this->listener)($this->event->reveal()));
        $this->event->versionRemoved()->shouldHaveBeenCalled();
    }
}
