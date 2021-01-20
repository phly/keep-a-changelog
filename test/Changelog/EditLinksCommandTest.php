<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\EditChangelogLinksEvent;
use Phly\KeepAChangelog\Changelog\EditLinksCommand;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditLinksCommandTest extends TestCase
{
    use ExecuteCommandTrait;
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
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

        $event = $this->prophesize(EditChangelogLinksEvent::class);
        $event->failed()->willReturn($failureStatus);

        $dispatcher
            ->dispatch(Argument::that(
                function ($event) use ($input, $output, $dispatcher) {
                    TestCase::assertInstanceOf(EditChangelogLinksEvent::class, $event);
                    TestCase::assertSame($input->reveal(), $event->input());
                    TestCase::assertSame($output->reveal(), $event->output());
                    TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());

                    return $event;
                }
            ))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new EditLinksCommand($dispatcher->reveal());

        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
