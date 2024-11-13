<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\RemoveCommand;
use Phly\KeepAChangelog\ConfigCommand\RemoveConfigEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommandTest extends TestCase
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
    public function testDispatchesRemoveConfigEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->createMock(RemoveConfigEvent::class);

        $expected->expects($this->once())->method('failed')->willReturn($failedFlag);

        $this->input
            ->expects($this->atLeast(2))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['local', true],
                ['global', true],
            ]));

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (RemoveConfigEvent $event): bool {
                TestCase::assertInstanceOf(RemoveConfigEvent::class, $event);
                TestCase::assertSame($this->input, $event->input());
                TestCase::assertSame($this->output, $event->output());
                TestCase::assertTrue($event->removeLocal());
                TestCase::assertTrue($event->removeGlobal());
                return true;
            }))
            ->willReturn($expected);

        $command = new RemoveCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
