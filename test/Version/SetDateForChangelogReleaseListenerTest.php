<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\ChangelogParser;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ReadyLatestChangelogEvent;
use Phly\KeepAChangelog\Version\SetDateForChangelogReleaseListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use function array_merge;
use function date;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class SetDateForChangelogReleaseListenerTest extends TestCase
{
    private const CHANGELOG_ENTRY_TEMPLATE = <<<'EOC'
%s%s%s%s

### Added

- Added some things

### Changed

- Change ALL THE THINGS

### Deprecated

- Deprecated stuff we do not use

### Removed

- Removed things we previously deprecated

### Fixed

- Fixed a bug

EOC;

    /** @var null|string name of temporary file used during testing */
    private $tempFile;

    public function setUp()
    {
        $this->config = $this->prophesize(Config::class);
        $this->entry  = new ChangelogEntry();
        $this->event  = $this->prophesize(ReadyLatestChangelogEvent::class);
        $this->event->changelogEntry()->willReturn($this->entry);
        $this->event->releaseDate()->willReturn('2019-05-30');
        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function tearDown()
    {
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function invalidVersionAndDatePermutations() : iterable
    {
        $leaders    = [
            'no leader'          => '',
            'first-level header' => '# ',
            'third-level header' => '### ',
            'missing whitespace' => '##',
        ];
        $versions   = [
            'major version only' => '1',
            'minor version only' => '1.1',
            'too many dots'      => '1.1.1.1',
            'unknown suffix'     => '1.1.1x1',
        ];
        $separators = [
            'whitespace only'    => ' ',
            'non-dash character' => ' : ',
        ];
        $dates      = [
            'past date'    => '2018-05-30',
            'present date' => date('Y-m-d'),
            'future date'  => '2119-05-30',
        ];

        foreach ($leaders as $leaderType => $leader) {
            foreach ($versions as $versionType => $version) {
                foreach ($separators as $separatorType => $separator) {
                    foreach ($dates as $dateType => $date) {
                        $label = sprintf(
                            '%s :: %s :: %s :: %s',
                            $leaderType,
                            $versionType,
                            $separatorType,
                            $dateType
                        );
                        yield $label => [$leader, $version, $separator, $date];
                    }
                }
            }
        }
    }

    /**
     * @dataProvider invalidVersionAndDatePermutations
     */
    public function testInformsEventOfMalformedReleaseLines(
        string $leader,
        string $version,
        string $separator,
        string $date
    ) {
        $contents              = sprintf(self::CHANGELOG_ENTRY_TEMPLATE, $leader, $version, $separator, $date);
        $this->entry->contents = $contents;
        $this->event->malformedReleaseLine(explode("\n", $contents)[0])->shouldBeCalled();

        $listener = new SetDateForChangelogReleaseListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldNotHaveBeenCalled();
        $this->event->changelogReady()->shouldNotHaveBeenCalled();
    }

    public function validVersionAndDatePermutations() : iterable
    {
        $versions = [
            'major version' => ['2.0.0', 4, 22],
            'minor version' => ['1.2.0', 26, 22],
            'patch version' => ['0.1.1', 48, 22],
        ];
        $dates    = [
            'standard TBD'   => 'TBD',
            'UNRELEASED'     => 'UNRELEASED',
            'PRERELEASE'     => 'PRERELEASE',
            'US Date Format' => '05/30/2019',
        ];

        foreach ($versions as $versionType => $entryData) {
            foreach ($dates as $dateType => $date) {
                $label     = sprintf('%s :: %s', $versionType, $dateType);
                $arguments = array_merge($entryData, [$date]);
                yield $label => $arguments;
            }
        }
    }

    /**
     * @dataProvider validVersionAndDatePermutations
     */
    public function testSetsDateForChangelogEntry(
        string $version,
        int $index,
        int $length,
        string $date
    ) {
        $contents = sprintf(self::CHANGELOG_ENTRY_TEMPLATE, '## ', $version, ' - ', $date);

        $this->entry->contents = $contents;
        $this->entry->index    = $index;
        $this->entry->length   = $length;

        $this->tempFile = tempnam(sys_get_temp_dir(), 'KAC');
        file_put_contents($this->tempFile, file_get_contents(__DIR__ . '/../_files/CHANGELOG-MULTIPLE-UNRELEASED.md'));
        $this->config->changelogFile()->willReturn($this->tempFile);

        $this->event->changelogReady()->shouldBeCalled();

        $listener = new SetDateForChangelogReleaseListener();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->malformedReleaseLine(Argument::any())->shouldNotHaveBeenCalled();

        $changelog = file_get_contents($this->tempFile);
        $this->assertSame('2019-05-30', (new ChangelogParser())->findReleaseDateForVersion($changelog, $version));
    }
}
