<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpChangelogVersionListener;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;

class BumpChangelogVersionListenerTest extends TestCase
{
    /** @var string */
    private $tempFile;

    public function setUp()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents(
            $this->tempFile,
            file_get_contents(__DIR__ . '/../_files/CHANGELOG.md')
        );

        $this->config = $this->prophesize(Config::class);
        $this->config->changelogFile()->willReturn($this->tempFile);

        $this->event = $this->prophesize(BumpChangelogVersionEvent::class);
        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function tearDown()
    {
        unlink($this->tempFile);
    }

    public function testBumpsToVersionProvidedInEvent()
    {
        $this->event->version()->willReturn('3.2.1');
        $this->event->bumpedChangelog('3.2.1')->shouldBeCalled();

        $listener = new BumpChangelogVersionListener();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function bumpMethods() : iterable
    {
        yield 'bugfix' => ['bumpBugfixVersion', '2.0.1'];
        yield 'minor'  => ['bumpMinorVersion', '2.1.0'];
        yield 'major'  => ['bumpMajorVersion', '3.0.0'];
    }

    /**
     * @dataProvider bumpMethods
     *
     * @param string $bumpMethod Method to use on internal ChangelogBump instance
     * @param string $expected Version expected back after bumping
     */
    public function testBumpsUsingMethodProvidedInEvent(string $bumpMethod, string $expected)
    {
        $this->event->version()->willReturn(null);
        $this->event->bumpMethod()->willReturn($bumpMethod);
        $this->event->bumpedChangelog($expected)->shouldBeCalled();

        $listener = new BumpChangelogVersionListener();

        $this->assertNull($listener($this->event->reveal()));
    }
}
