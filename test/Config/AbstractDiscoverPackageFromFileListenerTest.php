<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config\AbstractDiscoverPackageFromFileListener;
use Phly\KeepAChangelog\Config\PackageNameDiscovery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractDiscoverPackageFromFileListenerTest extends TestCase
{
    protected PackageNameDiscovery&MockObject $event;

    abstract public function createListener(): AbstractDiscoverPackageFromFileListener;

    protected function setUp(): void
    {
        $this->event = $this->createMock(PackageNameDiscovery::class);
    }

    public function testReturnsEarlyIfEventIndicatesPackageWasFound()
    {
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(true);
        $this->event->expects($this->never())->method('foundPackage');

        $listener = $this->createListener();

        $this->assertNull($listener($this->event));
    }

    public function testReturnsEarlyIfPackageFileIsNotReadable()
    {
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->never())->method('foundPackage');

        $listener             = $this->createListener();
        $listener->packageDir = __DIR__;

        $this->assertNull($listener($this->event));
    }

    public function testReturnsEarlyIfPackageFileDoesNotContainPackageName()
    {
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->never())->method('foundPackage');

        $listener             = $this->createListener();
        $listener->packageDir = __DIR__ . '/../_files/package_root/malformed';

        $this->assertNull($listener($this->event));
    }

    public function testReportsPackageFoundToEventWhenSuccessful()
    {
        $this->event->expects($this->once())->method('packageWasFound')->willReturn(false);
        $this->event->expects($this->once())->method('foundPackage')->with('some/package');

        $listener             = $this->createListener();
        $listener->packageDir = __DIR__ . '/../_files/package_root';

        $this->assertNull($listener($this->event));
    }
}
