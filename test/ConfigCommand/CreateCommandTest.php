<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\CreateCommand;
use Phly\KeepAChangelog\ConfigCommand\CreateConfigEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function failureStatus(): iterable
    {
        yield 'failed'    => [true, 1];
        yield 'succeeded' => [false, 0];
    }

    /**
     * @dataProvider failureStatus
     */
    public function testDispatchesCreateConfigEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->prophesize(CreateConfigEvent::class);
        $expected->failed()->willReturn($failedFlag);

        $this->input->getOption('local')->willReturn(true);
        $this->input->getOption('global')->willReturn(true);
        $this->input->getOption('changelog')->willReturn('changelog.txt');

        $input  = $this->input->reveal();
        $output = $this->output->reveal();

        $this->dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output) {
                TestCase::assertInstanceOf(CreateConfigEvent::class, $event);
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertTrue($event->createLocal());
                TestCase::assertTrue($event->createGlobal());
                TestCase::assertSame('changelog.txt', $event->customChangelog());
                return $event;
            }))
            ->will(function () use ($expected) {
                return $expected->reveal();
            });

        $command = new CreateCommand($this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneAndEmitsErrorMessageWhenNeitherLocalNorGlobalOptionProvided(): void
    {
        $input = $this->input;
        $input->getOption('local')->willReturn(null);
        $input->getOption('global')->willReturn(null);

        $output = $this->output;
        $output
            ->writeln(Argument::containingString('--local|-l OR --global|-g'))
            ->shouldBeCalled();

        $dispatcher = $this->dispatcher;
        $dispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $command = new CreateCommand($this->dispatcher->reveal());

        $this->assertSame(1, $this->executeCommand($command));
    }
}
