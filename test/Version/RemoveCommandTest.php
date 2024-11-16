<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Version;

use Phly\KeepAChangelog\Version\RemoveChangelogVersionEvent;
use Phly\KeepAChangelog\Version\RemoveCommand;
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
    public function testDispatchesRemoveChangelogVersionEventAndReturnsIntBasedOnFailureStatus(
        bool $failedFlag,
        int $expectedStatus
    ) {
        $expected = $this->createMock(RemoveChangelogVersionEvent::class);
        $expected->expects($this->once())->method('failed')->willReturn($failedFlag);

        $this->input->method('getArgument')->with('version')->willReturn('1.2.3');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (RemoveChangelogVersionEvent $event): bool {
                TestCase::assertSame($this->input, $event->input());
                TestCase::assertSame($this->output, $event->output());
                TestCase::assertSame('1.2.3', $event->version());
                return true;
            }))
            ->willReturn($expected);

        $command = new RemoveCommand($this->dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
