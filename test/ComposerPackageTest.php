<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\ComposerPackage;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;

class ComposerPackageTest extends TestCase
{
    public function setUp()
    {
        $this->package = new ComposerPackage();
    }

    public function testGetNameRaisesExceptionIfComposerFileIsNotFoundAtGivenPath()
    {
        $this->expectException(Exception\ComposerNotFoundException::class);
        $this->package->getName(realpath(__DIR__));
    }

    public function testGetNameRaisesExceptionIfComposerFileDoesNotContainPackageName()
    {
        $this->expectException(Exception\PackageNotFoundException::class);
        $this->package->getName(realpath(__DIR__) . '/_files/invalid-composer');
    }

    public function testReturnsPackageNameWhenComposerFoundAndFileContainsPackageName()
    {
        $name = $this->package->getName(realpath(__DIR__ . '/../'));
        $this->assertSame('phly/keep-a-changelog', $name);
    }
}
