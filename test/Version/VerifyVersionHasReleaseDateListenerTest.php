<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use Phly\KeepAChangelog\Version\VerifyVersionHasReleaseDateListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyVersionHasReleaseDateListenerTest extends TestCase
{
    public function testDoesNothingIfChangelogHasAssociatedReleaseDate(): void
    {
        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn(__DIR__ . '/../_files/CHANGELOG.md')->shouldBeCalled();

        /** @var TagReleaseEvent|ObjectProphecy $event */
        $event = $this->prophesize(TagReleaseEvent::class);
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();
        $event->version()->willReturn('1.1.0')->shouldBeCalledTimes(1);
        $event->taggingFailed()->shouldNotBeCalled();
        $event->output()->shouldNotBeCalled();

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event->reveal()));
    }

    public function testNotifiesEventTaggingFailedIfChangelogDoesNotHaveReleaseDate(): void
    {
        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn(__DIR__ . '/../_files/CHANGELOG.md')->shouldBeCalled();

        /** @var OutputInterface|ObjectProphecy $output */
        $output = $this->prophesize(OutputInterface::class);
        $output
            ->writeln(Argument::containingString('Version 2.0.0 does not have a release date'))
            ->shouldBeCalled();
        $output
            ->writeln(Argument::containingString('version:ready'))
            ->shouldBeCalled();

        /** @var TagReleaseEvent|ObjectProphecy $event */
        $event = $this->prophesize(TagReleaseEvent::class);
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();
        $event->version()->willReturn('2.0.0')->shouldBeCalled();
        $event->taggingFailed()->shouldBeCalled();
        $event->output()->will([$output, 'reveal'])->shouldBeCalled();

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event->reveal()));
    }
}
