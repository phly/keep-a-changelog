<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\EditCommand;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    protected function setUp() : void
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
    public function testDispatchesEditConfigEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->prophesize(EditConfigEvent::class);
        $expected->failed()->willReturn($failedFlag);

        $this->input->getOption('local')->willReturn(true);
        $this->input->getOption('global')->willReturn(true);
        $this->input->getOption('editor')->willReturn('custom-editor');

        $input  = $this->input->reveal();
        $output = $this->output->reveal();

        $this->dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output) {
                TestCase::assertInstanceOf(EditConfigEvent::class, $event);
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertTrue($event->editLocal());
                TestCase::assertTrue($event->editGlobal());
                TestCase::assertSame('custom-editor', $event->editor());
                return $event;
            }))
            ->will(function () use ($expected) {
                return $expected->reveal();
            });

        $command = new EditCommand($this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
