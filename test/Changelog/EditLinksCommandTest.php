<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\EditChangelogLinksEvent;
use Phly\KeepAChangelog\Changelog\EditLinksCommand;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditLinksCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function statuses(): iterable
    {
        yield 'failure' => [$failed = true, 1];
        yield 'success' => [$failed = false, 0];
    }

    /**
     * @dataProvider statuses
     */
    public function testReturnsExpectedExitCodeBasedOnEventDispatchStatus(
        bool $failureStatus,
        int $expectedStatus
    ) {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;

        $event = $this->createMock(EditChangelogLinksEvent::class);
        $event->expects($this->atLeastOnce())->method('failed')->willReturn($failureStatus);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                function ($event) use ($input, $output, $dispatcher) {
                    TestCase::assertInstanceOf(EditChangelogLinksEvent::class, $event);
                    TestCase::assertSame($input, $event->input());
                    TestCase::assertSame($output, $event->output());
                    TestCase::assertSame($dispatcher, $event->dispatcher());

                    return true;
                }
            ))
            ->willReturn($event);

        $command = new EditLinksCommand($dispatcher);

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
