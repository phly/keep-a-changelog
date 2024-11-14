<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\ConfigCommand\RemoveConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveConfigEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function createEvent(bool $removeLocal, bool $removeGlobal): RemoveConfigEvent
    {
        return new RemoveConfigEvent($this->input, $this->output, $removeLocal, $removeGlobal);
    }

    public function testImplementsIOInterface(): RemoveConfigEvent
    {
        $event = $this->createEvent(true, true);
        $this->assertInstanceOf(IOInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsIOInterface
     */
    public function testImplementsStoppableEventInterface(RemoveConfigEvent $event): RemoveConfigEvent
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsStoppableEventInterface
     */
    public function testStopPropagationAndFailureStatusAreFalseByDefault(RemoveConfigEvent $event): RemoveConfigEvent
    {
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
        return $event;
    }

    /**
     * @depends testStopPropagationAndFailureStatusAreFalseByDefault
     */
    public function testConstructorValuesAreAccessible(RemoveConfigEvent $event)
    {
        // Cannot do assertSame here as in different test; values change on setUp
        $this->assertInstanceOf(InputInterface::class, $event->input());
        $this->assertInstanceOf(OutputInterface::class, $event->output());

        $this->assertTrue($event->removeLocal());
        $this->assertTrue($event->removeGlobal());
    }

    public function testNotifyingDeletedConfigFileEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent(true, true);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Removed the file changelog.txt'));

        $this->assertNull($event->deletedConfigFile('changelog.txt'));

        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testAbortingEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent(true, true);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Aborted removal of changelog.txt'));

        $this->assertNull($event->abort('changelog.txt'));

        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingConfigFileNotFoundEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent(true, true);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Cannot remove config file changelog.txt'));

        $this->assertNull($event->configFileNotFound('changelog.txt'));

        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingErrorRemovingConfigEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Operation failed', $message),
                    2       => TestCase::assertStringContainsString('Unable to remove the file changelog.txt', $message),
                    default => true,
                };
                return true;
            }));

        $this->assertNull($event->errorRemovingConfig('changelog.txt'));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testNotifyingMissingOptionsEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Missing options!', $message),
                    2       => TestCase::assertStringContainsString('One or more', $message),
                    default => true,
                };
                return true;
            }));

        $this->assertNull($event->missingOptions());

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
