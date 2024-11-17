<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditConfigEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function createEvent(bool $editLocal, bool $editGlobal): EditConfigEvent
    {
        return new EditConfigEvent($this->input, $this->output, $editLocal, $editGlobal);
    }

    public function testImplementsIOInterface(): EditConfigEvent
    {
        $event = $this->createEvent(true, true);
        $this->assertInstanceOf(IOInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsIOInterface
     */
    public function testImplementsEditorAwareInterface(EditConfigEvent $event): EditConfigEvent
    {
        $this->assertInstanceOf(EditorAwareEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsEditorAwareInterface
     */
    public function testImplementsStoppableEventInterface(EditConfigEvent $event): EditConfigEvent
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsStoppableEventInterface
     */
    public function testStopPropagationAndFailureStatusAreFalseByDefault(EditConfigEvent $event): EditConfigEvent
    {
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
        return $event;
    }

    /**
     * @depends testStopPropagationAndFailureStatusAreFalseByDefault
     */
    public function testConstructorValuesAreAccessible(EditConfigEvent $event): EditConfigEvent
    {
        // Cannot do assertSame here as in different test; values change on setUp
        $this->assertInstanceOf(InputInterface::class, $event->input());
        $this->assertInstanceOf(OutputInterface::class, $event->output());

        $this->assertTrue($event->editLocal());
        $this->assertTrue($event->editGlobal());
        $this->assertNull($event->editor());
        return $event;
    }

    /**
     * @depends testConstructorValuesAreAccessible
     */
    public function testEditorIsNotPresentByDefault(EditConfigEvent $event)
    {
        $this->assertNull($event->editor());
    }

    public function testEditorIsMutableViaDiscoverEditorMethod()
    {
        $event = $this->createEvent(true, true);

        $event->discoverEditor('custom-editor');

        $this->assertSame('custom-editor', $event->editor());
    }

    public function testMarkingEditCompleteEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent(true, true);
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Completed editing keep-a-changelog.ini'));

        $this->assertNull($event->editComplete('keep-a-changelog.ini'));

        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testMarkingConfigFileNotFoundEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $invokedCount = $this->atLeast(1);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString(
                        'Could not find config file keep-a-changelog.ini',
                        $message
                    ),
                    default => true,
                };
                return true;
            }));

        $this->assertNull($event->configFileNotFound('keep-a-changelog.ini'));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testMarkingEditFailedEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $invokedCount = $this->atLeast(1);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString(
                        'Editing config file keep-a-changelog.ini failed',
                        $message
                    ),
                    default => true,
                };
                return true;
            }));

        $this->assertNull($event->editFailed('keep-a-changelog.ini'));

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testMarkingTooManyOptionsEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $invokedCount = $this->exactly(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1 => TestCase::assertStringContainsString('Too many options', $message),
                    2 => TestCase::assertStringContainsString('only use ONE', $message),
                };
                return true;
            }));

        $this->assertNull($event->tooManyOptions());

        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
