<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Unreleased;

use Phly\KeepAChangelog\Unreleased\PromoteEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PromoteEventTest extends TestCase
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    public function setUp() : void
    {
        $this->input      = $this->prophesize(InputInterface::class)->reveal();
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class)->reveal();
    }

    public function testConstructorSetsVersionNewVersionAndReleaseDate() : void
    {
        $event = new PromoteEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '2.5.0',
            '2020-07-16'
        );

        $this->assertSame('unreleased', $event->version());
        $this->assertSame('2.5.0', $event->newVersion());
        $this->assertSame('2020-07-16', $event->releaseDate());
    }

    public function testCallingDidNotPromoteStopsPropagationAndEmitsOutput() : void
    {
        $event = new PromoteEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '2.5.0',
            '2020-07-16'
        );

        $this->output->writeln(Argument::containingString('Invalid date'))->shouldBeCalled();

        $event->didNotPromote();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testCallingChangelogReadyEmitsOutputButDoesNotStopPropagation() : void
    {
        $event = new PromoteEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '2.5.0',
            '2020-07-16'
        );

        $this->output
            ->writeln(Argument::containingString(
                'Renamed Unreleased entry to "2.5.0" with release date of "2020-07-16"'
            ))
            ->shouldBeCalled();

        $event->changelogReady();
        $this->assertFalse($event->isPropagationStopped());
    }
}
