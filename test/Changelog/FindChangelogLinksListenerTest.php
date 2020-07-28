<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class FindChangelogLinksListenerTest extends TestCase
{
    protected function setUp(): void
    {
        $voidReturn = function () {
        };

        $this->config = $this->prophesize(Config::class);
        $this->event  = $this->prophesize(EditChangelogLinksEvent::class);

        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->noLinksDiscovered()->will($voidReturn);
        $this->event->discoveredLinks(Argument::any())->will($voidReturn);
    }

    public function testNotifesEventWhenNoLinksDiscovered()
    {
        $this->config->changelogFile()->willReturn(__DIR__ . '/../_files/CHANGELOG.md');
        $listener = new FindChangelogLinksListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->noLinksDiscovered()->shouldHaveBeenCalled();
        $this->event->discoveredLinks()->shouldNotHaveBeenCalled();
    }

    public function testNotifesEventWhenLinksDiscovered()
    {
        $this->config->changelogFile()->willReturn(__DIR__ . '/../_files/CHANGELOG-WITH-LINKS.md');
        $listener = new FindChangelogLinksListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->noLinksDiscovered()->shouldNotHaveBeenCalled();
        $this->event
            ->discoveredLinks(Argument::that(function ($links) {
                TestCase::assertInstanceOf(ChangelogEntry::class, $links);
                TestCase::assertSame(70, $links->index);
                TestCase::assertSame(3, $links->length);
                return $links;
            }))
            ->shouldHaveBeenCalled();
    }
}
