<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\RemoveChangelogVersionEvent;
use Phly\KeepAChangelog\Version\RemoveCommand;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommandTest extends TestCase
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
    public function testDispatchesRemoveChangelogVersionEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->prophesize(RemoveChangelogVersionEvent::class);
        $expected->failed()->willReturn($failedFlag);

        $this->input->getArgument('version')->willReturn('1.2.3');

        $input  = $this->input->reveal();
        $output = $this->output->reveal();

        $this->dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output) {
                TestCase::assertInstanceOf(RemoveChangelogVersionEvent::class, $event);
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertSame('1.2.3', $event->version());
                return $event;
            }))
            ->will(function () use ($expected) {
                return $expected->reveal();
            });

        $command = new RemoveCommand($this->dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
