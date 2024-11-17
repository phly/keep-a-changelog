<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReadyLatestChangelogEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class ReadyLatestChangelogEventTest extends TestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
    }

    public function testNotInFailureStateAndPropagationIsNotStoppedByDefault(): ReadyLatestChangelogEvent
    {
        $event = new ReadyLatestChangelogEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '2019-06-01',
            '1.2.3'
        );

        $this->assertFalse($event->failed());
        $this->assertFalse($event->isPropagationStopped());

        return $event;
    }

    /**
     * @depends testNotInFailureStateAndPropagationIsNotStoppedByDefault
     */
    public function testReleaseDateAndVersionAreAccessible(ReadyLatestChangelogEvent $event)
    {
        $this->assertSame('2019-06-01', $event->releaseDate());
        $this->assertSame('1.2.3', $event->version());
    }

    public function testNotifyingEventOfMalformedReleaseLineStopsPropagationAndMarksAsFailure()
    {
        $releaseLine = 'This is a bad release line';
        $event       = new ReadyLatestChangelogEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '2019-06-01',
            '1.2.3'
        );

        $invokedCount = $this->atLeast(7);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount, $releaseLine): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('malformed release line', $message),
                    2       => TestCase::assertStringContainsString('Must be in the following format', $message),
                    3       => TestCase::assertStringContainsString('## <version> - TBD', $message),
                    4       => TestCase::assertStringContainsString('follows semantic versioning rules', $message),
                    5       => TestCase::assertSame('', $message),
                    6       => TestCase::assertStringContainsString('Discovered:', $message),
                    7       => TestCase::assertStringContainsString($releaseLine, $message),
                    default => true,
                };
                return true;
            }));

        $this->assertNull($event->malformedReleaseLine($releaseLine));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function versionArguments(): iterable
    {
        yield 'null'  => [null, 'most recent changelog'];
        yield '1.2.3' => ['1.2.3', 'changelog version 1.2.3'];
    }

    /**
     * @dataProvider versionArguments
     */
    public function testMarkingChangelogReadyOutputsMessagesWithoutStoppingPropagationOrFailing(
        ?string $version,
        string $expectedPhrase
    ) {
        $expected = sprintf('Set release date of %s to "2019-06-01"', $expectedPhrase);
        $event    = new ReadyLatestChangelogEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '2019-06-01',
            $version
        );

        $this->output->expects($this->once())->method('writeln')->with($this->stringContains($expected));

        $this->assertNull($event->changelogReady());

        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }
}
