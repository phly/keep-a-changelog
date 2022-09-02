<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\TagReleaseEvent;
use Phly\KeepAChangelog\Version\VerifyVersionHasReleaseDateListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyVersionHasReleaseDateListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testDoesNothingIfChangelogHasAssociatedReleaseDate(): void
    {
        $config = $this->prophesize(Config::class);
        $config->changelogFile()->willReturn(__DIR__ . '/../_files/CHANGELOG.md')->shouldBeCalled();

        /** @var TagReleaseEvent|ObjectProphecy $event */
        $event = $this->prophesize(TagReleaseEvent::class);
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();
        $event->version()->willReturn('1.1.0')->shouldBeCalledTimes(1);
        $event->changelogMissingDate()->shouldNotBeCalled();
        $event->output()->shouldNotBeCalled();

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event->reveal()));
    }

    public function testNotifiesEventTaggingFailedIfChangelogDoesNotHaveReleaseDate(): void
    {
        $config = $this->prophesize(Config::class);
        $output = $this->prophesize(OutputInterface::class);
        $event  = $this->prophesize(TagReleaseEvent::class);

        $config->changelogFile()->willReturn(__DIR__ . '/../_files/CHANGELOG.md')->shouldBeCalled();
        $event->config()->will([$config, 'reveal'])->shouldBeCalled();
        $event->version()->willReturn('2.0.0')->shouldBeCalled();
        $event->changelogMissingDate()->shouldBeCalled();

        $listener = new VerifyVersionHasReleaseDateListener();
        $this->assertNull($listener($event->reveal()));
    }
}
