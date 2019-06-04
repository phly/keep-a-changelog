<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\Common\IOInterface;
use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowConfigEventTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->output->writeln(Argument::any())->willReturn(null);
    }

    public function createEvent(bool $showLocal, bool $showGlobal) : ShowConfigEvent
    {
        return new ShowConfigEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            $showLocal,
            $showGlobal
        );
    }

    public function testImplementsIOInterface() : ShowConfigEvent
    {
        $event = $this->createEvent(true, true, 'changelog.txt');
        $this->assertInstanceOf(IOInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsIOInterface
     */
    public function testImplementsStoppableEventInterface(ShowConfigEvent $event) : ShowConfigEvent
    {
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        return $event;
    }

    /**
     * @depends testImplementsStoppableEventInterface
     */
    public function testStopPropagationAndFailureStatusAreFalseByDefault(ShowConfigEvent $event) : ShowConfigEvent
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

    public function mergeFlags()
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

        $this->assertNull($event->displayConfig($config, $type, $location));

        $this->output
            ->writeln(Argument::containingString('Showing local configuration (.keep-a-changelog.ini)'))
            ->shouldHaveBeenCalled();
        $this->output->writeln($config)->shouldHaveBeenCalled();
        $this->output->writeln('')->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testDisplayMergedConfigEmitsConfigurationAndStopsPropagationWithoutFailure()
    {
        $event  = $this->createEvent(true, true);
        $config = 'This is the config';

        $this->assertNull($event->displayMergedConfig($config));

        $this->output
            ->writeln(Argument::containingString('Showing merged configuration'))
            ->shouldHaveBeenCalled();
        $this->output->writeln($config)->shouldHaveBeenCalled();
        $this->output->writeln('')->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertFalse($event->failed());
    }

    public function testNotifyingConfigIsNotReadableEmitsOutputAndStopsPropagationWithFailure()
    {
        $event = $this->createEvent(true, true);

        $this->assertNull($event->configIsNotReadable('keep-a-changelog.ini', 'global'));

        $this->output
            ->writeln(Argument::containingString('Unable to read configuration'))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString('global configuration file "keep-a-changelog.ini"'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }
}
