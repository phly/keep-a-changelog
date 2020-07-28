<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\ConfigCommand\RemoveConfigEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveConfigEventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->output->writeln(Argument::any())->willReturn(null);
    }

    public function createEvent(bool $removeLocal, bool $removeGlobal): RemoveConfigEvent
    {
        return new RemoveConfigEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $removeLocal,
            $removeGlobal
        );
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

        $this->assertNull($event->deletedConfigFile('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Removed the file changelog.txt'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testAbortingEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->abort('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Aborted removal of changelog.txt'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingConfigFileNotFoundEmitsOutputWithoutStoppingPropagationOrFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->configFileNotFound('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Cannot remove config file changelog.txt'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingErrorRemovingConfigEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->errorRemovingConfig('changelog.txt'));

        $this->output
            ->writeln(Argument::containingString('Operation failed'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('Unable to remove the file changelog.txt'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testNotifyingMissingOptionsEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->missingOptions());

        $this->output
            ->writeln(Argument::containingString('Missing options!'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('One or more'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
