<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\CreateReleaseNameListener;
use Phly\KeepAChangelog\Version\ReleaseEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class CreateReleaseNameListenerTest extends TestCase
{
    private InputInterface&MockObject $input;
    private Config&MockObject $config;
    private ReleaseEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->event  = $this->createMock(ReleaseEvent::class);

        $this->event->expects($this->any())->method('input')->willReturn($this->input);
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testSetsReleaseNameFromInputOptionWhenPresent()
    {
        $this->config->expects($this->never())->method('package');
        $this->event->expects($this->once())->method('setReleaseName')->with('some/package 1.2.3');
        $this->event->expects($this->never())->method('version');
        $this->input->expects($this->once())->method('getOption')->with('name')->willReturn('some/package 1.2.3');

        $listener = new CreateReleaseNameListener();

        $this->assertNull($listener($this->event));
    }

    public function testSetsReleaseNameBasedOnPackageAndVersionWhenNoInputOptionPresent()
    {
        $this->config->expects($this->once())->method('package')->willReturn('some/package');
        $this->event->expects($this->once())->method('setReleaseName')->with('package 1.2.3');
        $this->event->expects($this->once())->method('version')->willReturn('1.2.3');
        $this->input->expects($this->once())->method('getOption')->with('name')->willReturn(null);

        $listener = new CreateReleaseNameListener();

        $this->assertNull($listener($this->event));
    }
}
