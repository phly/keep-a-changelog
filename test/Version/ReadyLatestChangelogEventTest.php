<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ReadyLatestChangelogEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

class ReadyLatestChangelogEventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $this->input      = $this->prophesize(InputInterface::class)->reveal();
        $this->output     = $this->prophesize(OutputInterface::class);
    }

    public function testNotInFailureStateAndPropagationIsNotStoppedByDefault(): ReadyLatestChangelogEvent
    {
        $event = new ReadyLatestChangelogEvent(
            $this->input,
            $this->output->reveal(),
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
            $this->output->reveal(),
            $this->dispatcher,
            '2019-06-01',
            '1.2.3'
        );

        $this->output->writeln(Argument::any())->willReturn(null);

        $this->assertNull($event->malformedReleaseLine($releaseLine));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());

        $this->output->writeln(Argument::containingString('malformed release line'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('Must be in the following format'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('## <version> - TBD'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('follows semantic versioning rules'))->shouldHaveBeenCalled();
        $this->output->writeln('')->shouldHaveBeenCalled();
        $this->output->writeln('Discovered:')->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString($releaseLine))->shouldHaveBeenCalled();
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
        $event = new ReadyLatestChangelogEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '2019-06-01',
            $version
        );

        $this->output->writeln(Argument::any())->willReturn(null);

        $this->assertNull($event->changelogReady());

        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());

        $expected = sprintf('Set release date of %s to "2019-06-01"', $expectedPhrase);
        $this->output->writeln(Argument::containingString($expected))->shouldHaveBeenCalled();
    }
}
