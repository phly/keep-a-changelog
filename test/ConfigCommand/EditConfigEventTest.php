<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\EditorAwareEventInterface;
use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditConfigEventTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->output->writeln(Argument::any())->willReturn(null);
    }

    public function createEvent(bool $editLocal, bool $editGlobal) : EditConfigEvent
    {
        return new EditConfigEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $editLocal,
            $editGlobal
        );
    }

    public function testImplementsIOInterface() : EditConfigEvent
    {
        $event = $this->createEvent(true, true);
        $this->assertInstanceOf(IOInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsIOInterface
     */
    public function testImplementsEditorAwareInterface(EditConfigEvent $event) : EditConfigEvent
    {
        $this->assertInstanceOf(EditorAwareEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsEditorAwareInterface
     */
    public function testImplementsStoppableEventInterface(EditConfigEvent $event) : EditConfigEvent
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsStoppableEventInterface
     */
    public function testStopPropagationAndFailureStatusAreFalseByDefault(EditConfigEvent $event) : EditConfigEvent
    {
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
        return $event;
    }

    /**
     * @depends testStopPropagationAndFailureStatusAreFalseByDefault
     */
    public function testConstructorValuesAreAccessible(EditConfigEvent $event) : EditConfigEvent
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

        $this->assertNull($event->editComplete('keep-a-changelog.ini'));

        $this->output
            ->writeln(Argument::containingString('Completed editing keep-a-changelog.ini'))
            ->shouldHaveBeenCalled();
        $this->assertFalse($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testMarkingConfigFileNotFoundEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->configFileNotFound('keep-a-changelog.ini'));

        $this->output
            ->writeln(Argument::containingString('Could not find config file keep-a-changelog.ini'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testMarkingEditFailedEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->editFailed('keep-a-changelog.ini'));

        $this->output
            ->writeln(Argument::containingString('Editing config file keep-a-changelog.ini failed'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testMarkingTooManyOptionsEmitsOutputStopsPropagationAndMarksAsFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->tooManyOptions());

        $this->output
            ->writeln(Argument::containingString('Too many options'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('only use ONE'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
