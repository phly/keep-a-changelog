<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Common;

use Phly\KeepAChangelog\Common\ChangelogEntry;
use Phly\KeepAChangelog\Common\ChangelogEntryAwareEventInterface;
use Phly\KeepAChangelog\Common\DiscoverChangelogEntryListener;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DiscoverChangelogEntryListenerTest extends TestCase
{
    private string $changelog;
    private Config&MockObject $config;
    private ChangelogEntryAwareEventInterface&MockObject $event;

    protected function setUp(): void
    {
        $this->changelog = __DIR__ . '/../_files/CHANGELOG.md';

        $this->config = $this->createMock(Config::class);
        $this->config->expects($this->any())->method('changelogFile')->willReturn($this->changelog);

        $this->event = $this->createMock(ChangelogEntryAwareEventInterface::class);
        $this->event->expects($this->any())->method('config')->willReturn($this->config);
    }

    public function testNotifiesEventWhenEntryNotFound()
    {
        $this->event->expects($this->once())->method('version')->willReturn('7.6.5');
        $this->event->expects($this->once())->method('changelogEntryNotFound')->with($this->changelog, '7.6.5');
        $this->event->expects($this->never())->method('discoveredChangelogEntry');

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($this->event));
    }

    public function testNotifiesEventWithDiscoveredEntryOnSuccess()
    {
        $expected = <<<'EOC'
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
            
            
            EOC;

        $this->event->expects($this->once())->method('version')->willReturn('1.1.0');
        $this->event
            ->expects($this->once())
            ->method('discoveredChangelogEntry')
            ->with($this->callback(function (ChangelogEntry $entry) use ($expected): bool {
                TestCase::assertSame(26, $entry->index);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return true;
            }));
        $this->event->expects($this->never())->method('changelogEntryNotFound');

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($this->event));
    }

    public function testOmitsLinksWhenReturningLastEntryInFile()
    {
        $expected  = <<<'EOC'
            ## [0.1.0] - 2018-03-23
        
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
        $changelog = __DIR__ . '/../_files/CHANGELOG-WITH-LINKS.md';

        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('changelogFile')->willReturn($changelog);

        $event = $this->createMock(ChangelogEntryAwareEventInterface::class);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->atLeastOnce())->method('version')->willReturn('0.1.0');
        $event->expects($this->never())->method('changelogEntryNotFound');
        $event
            ->expects($this->once())
            ->method('discoveredChangelogEntry')
            ->with($this->callback(function (ChangelogEntry $entry) use ($expected): bool {
                TestCase::assertSame(48, $entry->index);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return true;
            }));

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($event));
    }

    public function unreleasedVersions(): iterable
    {
        yield 'null'       => [null];
        yield 'unreleased' => ['unreleased'];
    }

    /**
     * @dataProvider unreleasedVersions
     */
    public function testNotifiesEventWithDiscoveredEntryWhenUnreleasedSectionFound(?string $version): void
    {
        $changelog = __DIR__ . '/../_files/CHANGELOG-WITH-UNRELEASED-SECTION.md';
        $expected  = <<<'EOC'
            ## Unreleased
            
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

        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('changelogFile')->willReturn($changelog);

        $event = $this->createMock(ChangelogEntryAwareEventInterface::class);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->atLeastOnce())->method('version')->willReturn($version);
        $event->expects($this->never())->method('changelogEntryNotFound');
        $event
            ->expects($this->once())
            ->method('discoveredChangelogEntry')
            ->with($this->callback(function (ChangelogEntry $entry) use ($expected): bool {
                TestCase::assertSame(4, $entry->index, $entry->contents);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return true;
            }));

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($event));
    }

    /**
     * @dataProvider unreleasedVersions
     */
    public function testNotifiesEventWithDiscoveredEntryWhenLinkedUnreleasedSectionFound(?string $version): void
    {
        $changelog = __DIR__ . '/../_files/CHANGELOG-WITH-LINKED-UNRELEASED-SECTION.md';
        $expected  = <<<'EOC'
            ## [Unreleased]
            
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

        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('changelogFile')->willReturn($changelog);

        $event = $this->createMock(ChangelogEntryAwareEventInterface::class);
        $event->expects($this->atLeastOnce())->method('config')->willReturn($config);
        $event->expects($this->atLeastOnce())->method('version')->willReturn($version);
        $event->expects($this->never())->method('changelogEntryNotFound');
        $event
            ->expects($this->once())
            ->method('discoveredChangelogEntry')
            ->with($this->callback(function (ChangelogEntry $entry) use ($expected): bool {
                TestCase::assertSame(4, $entry->index, $entry->contents);
                TestCase::assertSame(22, $entry->length);
                TestCase::assertSame($expected, $entry->contents);
                return true;
            }));

        $listener = new DiscoverChangelogEntryListener();

        $this->assertNull($listener($event));
    }
}
