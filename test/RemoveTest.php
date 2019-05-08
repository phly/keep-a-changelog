<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\Remove;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class RemoveTest extends TestCase
{
    /** @var null|string */
    private $filename;

    public function setUp()
    {
        $this->filename = null;
        $this->output = $this->prophesize(OutputInterface::class);
        $this->remove = new Remove();
    }

    public function tearDown()
    {
        if ($this->filename) {
            if (file_exists($this->filename)) {
                unlink($this->filename);
            }
            $this->filename = null;
        }
    }

    protected function createChangelogFile() : string
    {
        $contents = file_get_contents(__DIR__ . '/_files/CHANGELOG.md');
        $this->filename = $filename = tempnam(sys_get_temp_dir(), 'CAK');
        file_put_contents($filename, $contents);
        return $filename;
    }

    public function testReturnsFalseWhenUnableToFindVersionInChangelog()
    {
        $filename = $this->createChangelogFile();
        $this->assertFalse(($this->remove)($this->output->reveal(), $filename, '1.10.0'));

        $this->output
             ->writeln(Argument::containingString('Unable to identify a changelog entry'))
             ->shouldHaveBeenCalledTimes(1);
    }

    public function testCanRemoveValidVersionFromChangelogFile()
    {
        $filename = $this->createChangelogFile();
        $this->assertTrue(($this->remove)($this->output->reveal(), $filename, '1.1.0'));

        $this->output
             ->writeln(Argument::containingString('Unable to identify a changelog entry'))
             ->shouldNotHaveBeenCalled();

        $expected = <<<'EOC'
# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.0 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2018-03-23

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

EOC;
        $actual = file_get_contents($filename);
        $this->assertEquals($actual, $expected);
    }
}
