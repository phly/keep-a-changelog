<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ChangelogEditor;
use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ReadyLatestChangelogEvent;
use Phly\KeepAChangelog\Version\SetDateForChangelogReleaseListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use function array_merge;
use function date;
use function explode;
use function sprintf;

class SetDateForChangelogReleaseListenerTest extends TestCase
{
    use ProphecyTrait;

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

    protected function setUp(): void
    {
        $voidReturn   = function () {
        };
        $this->config = $this->prophesize(Config::class);
        $this->entry  = new ChangelogEntry();
        $this->event  = $this->prophesize(ReadyLatestChangelogEvent::class);

        $this->event->changelogEntry()->willReturn($this->entry);
        $this->event->releaseDate()->willReturn('2019-05-30');
        $this->event->malformedReleaseLine(Argument::any())->will($voidReturn);
        $this->event->config()->will([$this->config, 'reveal']);
        $this->event->changelogReady()->will($voidReturn);
    }

    public function invalidVersionAndDatePermutations(): iterable
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

    public function validVersionAndDatePermutations(): iterable
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

        $this->config->changelogFile()->willReturn('changelog.txt');

        $editor = $this->prophesize(ChangelogEditor::class);
        $editor
            ->update(
                'changelog.txt',
                Argument::containingString(sprintf('## %s - 2019-05-30', $version)),
                $this->entry
            )
            ->will(function () {
            })
            ->shouldBeCalled();

        $listener                  = new SetDateForChangelogReleaseListener();
        $listener->changelogEditor = $editor->reveal();

        $this->assertNull($listener($this->event->reveal()));
        $this->event->malformedReleaseLine(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->changelogReady()->shouldHaveBeenCalled();
    }
}
