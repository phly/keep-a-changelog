<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\CreateNewChangelogEvent;
use Phly\KeepAChangelog\Changelog\NewCommand;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_merge;
use function sprintf;

class NewCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function inputOptions(): iterable
    {
        $cases = [
            'defaults' => [null, null, '0.1.0', false],
            'custom'   => ['1.0.0', true, '1.0.0', true],
        ];

        foreach ([true, false] as $failed) {
            foreach ($cases as $type => $defaults) {
                $name      = sprintf('%s - %s', $failed ? 'failed' : 'succeeded', $type);
                $arguments = array_merge([$failed], $defaults);
                yield $name => $arguments;
            }
        }
    }

    /**
     * @dataProvider inputOptions
     */
    public function testReturnsExpectedExitCodeBasedOnEventDispatchStatus(
        bool $failureStatus,
        ?string $initialVersion,
        ?bool $overwrite,
        string $expectedVersion,
        bool $expectedOverwrite
    ) {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;

        $input->getOption('initial-version')->willReturn($initialVersion);
        $input->getOption('overwrite')->willReturn($overwrite);

        $event = $this->prophesize(CreateNewChangelogEvent::class);
        $event->failed()->willReturn($failureStatus);

        $dispatcher
            ->dispatch(Argument::that(
                function ($event) use ($input, $output, $dispatcher, $expectedVersion, $expectedOverwrite) {
                    TestCase::assertInstanceOf(CreateNewChangelogEvent::class, $event);
                    TestCase::assertSame($input->reveal(), $event->input());
                    TestCase::assertSame($output->reveal(), $event->output());
                    TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                    TestCase::assertSame($expectedVersion, $event->version());
                    TestCase::assertSame($expectedOverwrite, $event->overwrite());

                    return $event;
                }
            ))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new NewCommand($dispatcher->reveal());

        $expectedStatus = $failureStatus ? 1 : 0;
        $this->assertSame($expectedStatus, $this->executeCommand($command));
    }
}
