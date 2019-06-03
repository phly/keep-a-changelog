<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\CreateConfigEvent;
use Phly\KeepAChangelog\Common\IOInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateConfigEventTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->output->writeln(Argument::any())->willReturn(null);
    }

    public function createEvent(
        bool $createLocal,
        bool $createGlobal,
        ?string $customChangelog = null
    ) : CreateConfigEvent {
        return new CreateConfigEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $createLocal,
            $createGlobal,
            $customChangelog
        );
    }

    public function testImplementsIOInterface() : CreateConfigEvent
    {
        $event = $this->createEvent(true, true, 'changelog.txt');
        $this->assertInstanceOf(IOInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsIOInterface
     */
    public function testImplementsStoppableEventInterface(CreateConfigEvent $event) : CreateConfigEvent
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsStoppableEventInterface
     */
    public function testStopPropagationAndFailureStatusAreFalseByDefault(CreateConfigEvent $event) : CreateConfigEvent
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

        $this->assertNull($event->fileExists('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Config file already exists at changelog.txt'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingConfigFileCreatedEmitsOutputWithoutStoppingPropagationOrFailing()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->createdConfigFile('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Created changelog.txt'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotyfingCreationFailedEmitsOutputStopsPropagationAndMarksAsFailed()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->creationFailed('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Failed creating config file'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('Verify'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
