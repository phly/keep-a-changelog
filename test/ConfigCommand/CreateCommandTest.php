<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\CreateCommand;
use Phly\KeepAChangelog\ConfigCommand\CreateConfigEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
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
        $input    = $this->input;
        $output   = $this->output;
        $expected = $this->createMock(CreateConfigEvent::class);

        $expected->expects($this->once())->method('failed')->willReturn($failedFlag);

        $this->input
            ->expects($this->atLeast(3))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['local', true],
                ['global', true],
                ['changelog', 'changelog.txt'],
            ]));

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($input, $output): bool {
                TestCase::assertInstanceOf(CreateConfigEvent::class, $event);
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                TestCase::assertTrue($event->createLocal());
                TestCase::assertTrue($event->createGlobal());
                TestCase::assertSame('changelog.txt', $event->customChangelog());
                return true;
            }))
            ->willReturn($expected);

        $command = new CreateCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }

    public function testExecutionReturnsOneAndEmitsErrorMessageWhenNeitherLocalNorGlobalOptionProvided(): void
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;

        $input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['local', false],
                ['global', false],
            ]));

        $output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('--local|-l OR --global|-g'));

        $dispatcher->expects($this->never())->method('dispatch');

        $command = new CreateCommand($this->dispatcher);

        $this->assertSame(1, $this->executeCommand($command));
    }
}
