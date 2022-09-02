<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config\AbstractDiscoverPackageFromFileListener;
use Phly\KeepAChangelog\Config\PackageNameDiscovery;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class AbstractDiscoverPackageFromFileListenerTest extends TestCase
{
    use ProphecyTrait;

    abstract public function createListener(): AbstractDiscoverPackageFromFileListener;

    protected function setUp(): void
    {
        $this->event = $this->prophesize(PackageNameDiscovery::class);
    }

    public function testReturnsEarlyIfEventIndicatesPackageWasFound()
    {
        $this->event->packageWasFound()->willReturn(true);

        $listener = $this->createListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReturnsEarlyIfPackageFileIsNotReadable()
    {
        $this->event->packageWasFound()->willReturn(false);

        $listener             = $this->createListener();
        $listener->packageDir = __DIR__;

        $this->assertNull($listener($this->event->reveal()));

        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReturnsEarlyIfPackageFileDoesNotContainPackageName()
    {
        $this->event->packageWasFound()->willReturn(false);

        $listener             = $this->createListener();
        $listener->packageDir = __DIR__ . '/../_files/package_root/malformed';

        $this->assertNull($listener($this->event->reveal()));

        $this->event->foundPackage(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testReportsPackageFoundToEventWhenSuccessful()
    {
        $this->event->packageWasFound()->willReturn(false);
        $this->event->foundPackage('some/package')->shouldBeCalled();

        $listener             = $this->createListener();
        $listener->packageDir = __DIR__ . '/../_files/package_root';

        $this->assertNull($listener($this->event->reveal()));
    }
}
