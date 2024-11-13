<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowConfigEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function createEvent(bool $showLocal, bool $showGlobal): ShowConfigEvent
    {
        return new ShowConfigEvent($this->input, $this->output, $showLocal, $showGlobal);
    }

    public function testImplementsIOInterface(): ShowConfigEvent
    {
        $event = $this->createEvent(true, true, 'changelog.txt');
        $this->assertInstanceOf(IOInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsIOInterface
     */
    public function testImplementsStoppableEventInterface(ShowConfigEvent $event): ShowConfigEvent
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsStoppableEventInterface
     */
    public function testStopPropagationAndFailureStatusAreFalseByDefault(ShowConfigEvent $event): ShowConfigEvent
    {
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
        return $event;
    }

    /**
     * @depends testStopPropagationAndFailureStatusAreFalseByDefault
     */
    public function testConstructorValuesAreAccessible(ShowConfigEvent $event)
    {
        // Cannot do assertSame here as in different test; values change on setUp
        $this->assertInstanceOf(InputInterface::class, $event->input());
        $this->assertInstanceOf(OutputInterface::class, $event->output());

        $this->assertTrue($event->showLocal());
        $this->assertTrue($event->showGlobal());
    }

    public function mergeFlags(): iterable
    {
        yield 'no local - no global - merged'  => [false, false, true];
        yield 'local - no global - not merged' => [true, false, false];
        yield 'no local - global - not merged' => [false, true, false];
        yield 'local - global - merged'        => [true, true, true];
    }

    /**
     * @dataProvider mergeFlags
     */
    public function testShowMergedFlagIsBasedOnShowLocalAndShowGlobalCombination(
        bool $showLocal,
        bool $showGlobal,
        bool $expectedMergeFlag
    ) {
        $event = $this->createEvent($showLocal, $showGlobal);
        $this->assertSame($expectedMergeFlag, $event->showMerged());
    }

    public function testDisplayConfigEmitsConfigurationAndStopsPropagationWithoutFailure()
    {
        $event    = $this->createEvent(true, false);
        $config   = 'This is the config';
        $type     = 'local';
        $location = '.keep-a-changelog.ini';

        $invokedCount = $this->exactly(3);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount, $config): bool {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString('Showing local configuration (.keep-a-changelog.ini)', $message),
                    2 => TestCase::assertEquals($config, $message),
                    3 => TestCase::assertEquals('', $message),
                };
                return true;
            }));

        $this->assertNull($event->displayConfig($config, $type, $location));
        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testDisplayMergedConfigEmitsConfigurationAndStopsPropagationWithoutFailure()
    {
        $event  = $this->createEvent(true, true);
        $config = 'This is the config';

        $invokedCount = $this->exactly(3);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount, $config): bool {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString('Showing merged configuration', $message),
                    2 => TestCase::assertEquals($config, $message),
                    3 => TestCase::assertEquals('', $message),
                };
                return true;
            }));

        $this->assertNull($event->displayMergedConfig($config));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingConfigIsNotReadableEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(true, true);

        $invokedCount = $this->exactly(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString('Unable to read configuration', $message),
                    2 => TestCase::assertStringContainsString('global configuration file "keep-a-changelog.ini"', $message),
                };
                return true;
            }));

        $this->assertNull($event->configIsNotReadable('keep-a-changelog.ini', 'global'));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
