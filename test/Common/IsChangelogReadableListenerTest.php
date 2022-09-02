<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\IsChangelogReadableListener;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use function realpath;

class IsChangelogReadableListenerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->config = $this->prophesize(Config::class);
        $this->event  = $this->prophesize(ReleaseEvent::class);

        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function testDoesNothingIfConfiguredChangelogFileIsReadable()
    {
        $changelogFile = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $this->config->changelogFile()->willReturn($changelogFile);

        $listener = new IsChangelogReadableListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->changelogFileIsUnreadable(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testTellsEventChangelogFileIsUnreadableIfProvidedFileIsNotReadable()
    {
        $expected = realpath(__DIR__) . '/CHANGELOG.md';
        $this->config->changelogFile()->willReturn($expected);
        $this->event->changelogFileIsUnreadable($expected)->shouldBeCalled();

        $listener = new IsChangelogReadableListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
