<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\CreateNewChangelogEvent;
use Phly\KeepAChangelog\Common;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateNewChangelogEventTest extends TestCase
{
    private Config&MockObject $config;
    private EventDispatcherInterface&MockObject $dispatcher;
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->config     = $this->createMock(Config::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
    }

    public function testPropagationIsNotStoppedByDefault(): CreateNewChangelogEvent
    {
        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '1.2.3',
            false
        );
        $this->assertFalse($event->isPropagationStopped());

        return $event;
    }

    /**
     * @depends testPropagationIsNotStoppedByDefault
     */
    public function testImplementsVersionAwareInterface(CreateNewChangelogEvent $event)
    {
        $this->assertInstanceOf(Common\VersionAwareEventInterface::class, $event);
    }

    public function booleanFlags(): iterable
    {
        yield 'true' => [true];
        yield 'false' => [false];
    }

    /**
     * @dataProvider booleanFlags
     */
    public function testOverwriteIsBasedOnConstructorArgument(bool $overwrite)
    {
        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '1.2.3',
            $overwrite
        );
        $this->assertSame($overwrite, $event->overwrite());
    }

    public function testNotifyingChangelogExistsStopsPropagationWithFailure()
    {
        $this->config->expects($this->atLeastOnce())->method('changelogFile')->willReturn('CHANGELOG.md');

        $invokedCount = $this->atLeast(2);

        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function ($message) use ($invokedCount) {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString('file exists', $message),
                    2 => TestCase::assertStringContainsString('use the --overwrite|-o option', $message),
                    default => null,
                };
                return true;
            }));

        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '1.2.3',
            false
        );
        $event->discoveredConfiguration($this->config);

        $this->assertNull($event->changelogExists());
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testNotifyingChangelogCreatedEmitsOutput()
    {
        $this->config->expects($this->atLeastOnce())->method('changelogFile')->willReturn('CHANGELOG.md');

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains(
                'new changelog in file "CHANGELOG.md" using initial version "1.2.3"'
            ));

        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            '1.2.3',
            false
        );
        $event->discoveredConfiguration($this->config);

        $event->createdChangelog();
    }
}
