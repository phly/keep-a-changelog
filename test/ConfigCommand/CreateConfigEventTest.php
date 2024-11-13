<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\ConfigCommand\CreateConfigEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateConfigEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->input  = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function createEvent(
        bool $createLocal,
        bool $createGlobal,
        ?string $customChangelog = null
    ): CreateConfigEvent {
        return new CreateConfigEvent(
            $this->input,
            $this->output,
            $createLocal,
            $createGlobal,
            $customChangelog
        );
    }

    public function testImplementsIOInterface(): CreateConfigEvent
    {
        $event = $this->createEvent(true, true, 'changelog.txt');
        $this->assertInstanceOf(IOInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsIOInterface
     */
    public function testImplementsStoppableEventInterface(CreateConfigEvent $event): CreateConfigEvent
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsStoppableEventInterface
     */
    public function testStopPropagationAndFailureStatusAreFalseByDefault(CreateConfigEvent $event): CreateConfigEvent
    {
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
        return $event;
    }

    /**
     * @depends testStopPropagationAndFailureStatusAreFalseByDefault
     */
    public function testConstructorValuesAreAccessible(CreateConfigEvent $event)
    {
        // Cannot do assertSame here as in different test; values change on setUp
        $this->assertInstanceOf(InputInterface::class, $event->input());
        $this->assertInstanceOf(OutputInterface::class, $event->output());

        $this->assertTrue($event->createLocal());
        $this->assertTrue($event->createGlobal());
        $this->assertSame('changelog.txt', $event->customChangelog());
    }

    public function testNotifyingFileExistsEmitsOutputWithoutStoppingPropagationOrFailing()
    {
        $event = $this->createEvent(true, true);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Config file already exists at changelog.txt'));

        $this->assertNull($event->fileExists('changelog.txt'));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingConfigFileCreatedEmitsOutputWithoutStoppingPropagationOrFailing()
    {
        $event = $this->createEvent(true, true);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Created changelog.txt'));

        $this->assertNull($event->createdConfigFile('changelog.txt'));
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotyfingCreationFailedEmitsOutputStopsPropagationAndMarksAsFailed()
    {
        $event = $this->createEvent(true, true);

        $invokedCount = $this->atLeast(2);
        $this->output
            ->expects($invokedCount)
            ->method('writeln')
            ->with($this->callback(function (string $message) use ($invokedCount): bool {
                match ($invokedCount->getInvocationCount()) {
                    1       => TestCase::assertStringContainsString('Failed creating config file', $message),
                    2       => TestCase::assertStringContainsString('Verify', $message),
                    default => true,
                };
                return true;
            }));

        $this->assertNull($event->creationFailed('changelog.txt'));
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
