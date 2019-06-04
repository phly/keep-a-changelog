<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\ShowCommand;
use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    public function setUp()
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function failureStatus() : iterable
    {
        yield 'failed'    => [true, 1];
        yield 'succeeded' => [false, 0];
    }

    /**
     * @dataProvider failureStatus
     */
    public function testDispatchesShowConfigEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->prophesize(ShowConfigEvent::class);
        $expected->failed()->willReturn($failedFlag);

        $this->input->getOption('local')->willReturn(true);
        $this->input->getOption('global')->willReturn(true);

        $input  = $this->input->reveal();
        $output = $this->output->reveal();

        $this->dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output) {
                TestCase::assertInstanceOf(ShowConfigEvent::class, $event);
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertTrue($event->showLocal());
                TestCase::assertTrue($event->showGlobal());
                return $event;
            }))
            ->will(function () use ($expected) {
                return $expected->reveal();
            });

        $command = new ShowCommand($this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
