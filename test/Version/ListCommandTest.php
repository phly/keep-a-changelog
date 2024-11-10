<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\ListCommand;
use Phly\KeepAChangelog\Version\ListVersionsEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommandTest extends TestCase
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
    public function testDispatchesListVersionsEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->createMock(ListVersionsEvent::class);
        $expected->expects($this->atLeastOnce())->method('failed')->willReturn($failedFlag);

        $input  = $this->input;
        $output = $this->output;

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($input, $output) {
                TestCase::assertInstanceOf(ListVersionsEvent::class, $event);
                TestCase::assertSame($input, $event->input());
                TestCase::assertSame($output, $event->output());
                return true;
            }))
            ->willReturn($expected);

        $command = new ListCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
