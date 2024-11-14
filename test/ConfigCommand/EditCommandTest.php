<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\EditCommand;
use Phly\KeepAChangelog\ConfigCommand\EditConfigEvent;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommandTest extends TestCase
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
    public function testDispatchesEditConfigEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->createMock(EditConfigEvent::class);
        $expected->expects($this->once())->method('failed')->willReturn($failedFlag);

        $this->input
            ->expects($this->atLeast(3))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['local', true],
                ['global', true],
                ['editor', 'custom-editor'],
            ]));

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (EditConfigEvent $event): bool {
                TestCase::assertSame($this->input, $event->input());
                TestCase::assertSame($this->output, $event->output());
                TestCase::assertTrue($event->editLocal());
                TestCase::assertTrue($event->editGlobal());
                TestCase::assertSame('custom-editor', $event->editor());
                return true;
            }))
            ->willReturn($expected);

        $command = new EditCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
