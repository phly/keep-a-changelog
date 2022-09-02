<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\ChangelogBump;
use Phly\KeepAChangelog\Exception\ChangelogEntriesNotFoundException;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class ChangelogBumpTest extends TestCase
{
    /** @var ChangelogBump */
    private $bumper;

    /** @var string */
    private $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents(
            $this->tempFile,
            file_get_contents(__DIR__ . '/../_files/CHANGELOG.md')
        );
        $this->bumper = new ChangelogBump($this->tempFile);
    }

    protected function tearDown(): void
    {
        unlink($this->tempFile);
    }

    public function testRetrievesLatestVersionCorrectly()
    {
        $this->assertEquals('2.0.0', $this->bumper->findLatestVersion());
    }

    public function bugfixVersions(): array
    {
        return [
            'zero-version'  => ['2.0.0', '2.0.1'],
            'nine-version'  => ['2.0.9', '2.0.10'],
            'alpha-version' => ['2.0.0alpha1', '2.0.1'],
            'beta-version'  => ['2.0.0beta1', '2.0.1'],
            'rc-version'    => ['2.0.0rc', '2.0.1'],
            'dev-version'   => ['2.0.0dev1', '2.0.1'],
        ];
    }

    /**
     * @dataProvider bugfixVersions
     */
    public function testBumpsBugfixVersionCorrectly(string $version, string $expected)
    {
        $this->assertEquals($expected, $this->bumper->bumpPatchVersion($version));
    }

    public function minorVersions(): array
    {
        return [
            'zero-version'  => ['2.1.0', '2.2.0'],
            'nine-version'  => ['2.1.9', '2.2.0'],
            'alpha-version' => ['2.2.0alpha1', '2.3.0'],
            'beta-version'  => ['2.2.0beta1', '2.3.0'],
            'rc-version'    => ['2.2.0rc', '2.3.0'],
            'dev-version'   => ['2.2.0dev1', '2.3.0'],
        ];
    }

    /**
     * @dataProvider minorVersions
     */
    public function testBumpsMinorVersionCorrectly(string $version, string $expected)
    {
        $this->assertEquals($expected, $this->bumper->bumpMinorVersion($version));
    }

    public function majorVersions(): array
    {
        return [
            'zero-version'  => ['2.1.0', '3.0.0'],
            'nine-version'  => ['2.1.9', '3.0.0'],
            'alpha-version' => ['2.2.0alpha1', '3.0.0'],
            'beta-version'  => ['2.2.0beta1', '3.0.0'],
            'rc-version'    => ['2.2.0rc', '3.0.0'],
            'dev-version'   => ['2.2.0dev1', '3.0.0'],
        ];
    }

    /**
     * @dataProvider majorVersions
     */
    public function testBumpsMajorVersionCorrectly(string $version, string $expected)
    {
        $this->assertEquals($expected, $this->bumper->bumpMajorVersion($version));
    }

    public function testUpdateChangelogPrependsNewEntry()
    {
        $expected = <<<'EOC'
# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.2.1 - TBD

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

## 1.1.0 - 2018-03-23

### Added

- Added a new feature.

### Changed

- Made some changes.

### Deprecated

- Nothing was deprecated.

### Removed

- Nothing was removed.

### Fixed

- Fixed some bugs.

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

        $this->bumper->updateChangelog('3.2.1');
        $this->assertEquals($expected, file_get_contents($this->tempFile));
    }

    public function testFindLatestVersionThrowsExceptionIfNoChangelogEntriesFound(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($this->tempFile, 'There are no changelog entries here');
        $bumper = new ChangelogBump($tempFile);

        $this->expectException(ChangelogEntriesNotFoundException::class);

        $bumper->findLatestVersion();
    }

    public function testUpdateChangelogPrependsNewEntryWhenChangelogHasExpandedHeading(): void
    {
        $expected = <<<'EOC'
# my/project's ChAnGeLoG of Notable Changes

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 - TBD

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

## 0.2.0 - TBD

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

## 0.1.1 - TBD

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

## 0.1.0 - 2020-08-31

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

        $tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents(
            $tempFile,
            file_get_contents(__DIR__ . '/../_files/CHANGELOG-WITH-EXPANDED-HEADING.md')
        );
        $bumper = new ChangelogBump($tempFile);

        $currentVersion = $bumper->findLatestVersion();
        $currentVersion = $bumper->bumpPatchVersion($currentVersion);
        $bumper->updateChangelog($currentVersion);

        $currentVersion = $bumper->bumpMinorVersion($currentVersion);
        $bumper->updateChangelog($currentVersion);

        $currentVersion = $bumper->bumpMajorVersion($currentVersion);
        $bumper->updateChangelog($currentVersion);

        $this->assertEquals($expected, file_get_contents($tempFile));
    }
}
