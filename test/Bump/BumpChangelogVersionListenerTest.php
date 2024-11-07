<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpChangelogVersionListener;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class BumpChangelogVersionListenerTest extends TestCase
{
    /** @var Config&MockObject **/
    private $config;

    /** @var BumpChangelogVersionEvent&MockObject **/
    private $event;

    /** @var string */
    private $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents(
            $this->tempFile,
            file_get_contents(__DIR__ . '/../_files/CHANGELOG.md')
        );

        $this->config = $this->createMock(Config::class);
        $this->config
            ->expects($this->any())
            ->method('changelogFile')
            ->willReturn($this->tempFile);

        $this->event = $this->createMock(BumpChangelogVersionEvent::class);
        $this->event
            ->expects($this->any())
            ->method('config')
            ->willReturn($this->config);
    }

    protected function tearDown(): void
    {
        unlink($this->tempFile);
    }

    public function testBumpsToVersionProvidedInEvent()
    {
        $this->event->expects($this->atLeastOnce())->method('version')->willReturn('3.2.1');
        $this->event->expects($this->atLeastOnce())->method('bumpedChangelog')->with('3.2.1');

        $listener = new BumpChangelogVersionListener();

        $this->assertNull($listener($this->event));
    }

    public function bumpMethods(): iterable
    {
        yield 'bugfix' => ['bumpPatchVersion', '2.0.1'];
        yield 'minor'  => ['bumpMinorVersion', '2.1.0'];
        yield 'major'  => ['bumpMajorVersion', '3.0.0'];
    }

    /**
     * @dataProvider bumpMethods
     * @param string $bumpMethod Method to use on internal ChangelogBump instance
     * @param string $expected   Version expected back after bumping
     */
    public function testBumpsUsingMethodProvidedInEvent(string $bumpMethod, string $expected)
    {
        $this->event->expects($this->atLeastOnce())->method('version')->willReturn(null);
        $this->event->expects($this->atLeastOnce())->method('bumpMethod')->willReturn($bumpMethod);
        $this->event->expects($this->atLeastOnce())->method('bumpedChangelog')->with($expected);

        $listener = new BumpChangelogVersionListener();

        $this->assertNull($listener($this->event));
    }
}
