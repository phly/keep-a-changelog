<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Version\ListVersionsEvent;
use Phly\KeepAChangelog\Version\ListVersionsListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class ListVersionsListenerTest extends TestCase
{
    public function testEmitsAllVersionsFoundWithCorrespondingDates()
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->atLeastOnce())->method('writeln')->with($this->isType('string'));
        $invokedCount = $this->atLeast(4);
        $output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function ($message) use ($invokedCount) {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString('Found the following versions', $message),
                    2 => TestCase::assertMatchesRegularExpression('/2\.0\.0\s+\(release date: TBD\)/', $message),
                    3 => TestCase::assertMatchesRegularExpression('/1\.1\.0\s+\(release date: 2018-03-23\)/', $message),
                    4 => TestCase::assertMatchesRegularExpression('/0\.1\.0\s+\(release date: 2018-03-23\)/', $message),
                    default => null,
                };
                return true;
            }));

        $config = $this->createMock(Config::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('changelogFile')
            ->willReturn(__DIR__ . '/../_files/CHANGELOG.md');

        $event = $this->createMock(ListVersionsEvent::class);
        $event->expects($this->any())->method('output')->willReturn($output);
        $event->expects($this->any())->method('config')->willReturn($config);

        $listener = new ListVersionsListener();

        $this->assertNull($listener($event));
    }
}
