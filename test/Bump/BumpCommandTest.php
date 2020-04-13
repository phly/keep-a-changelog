<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Bump\BumpCommand;
use Phly\KeepAChangelog\Exception;
use PhlyTest\KeepAChangelog\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpCommandTest extends TestCase
{
    use ExecuteCommandTrait;

    protected function setUp() : void
    {
        $this->input      = $this->prophesize(InputInterface::class);
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function testConstructorRaisesExceptionForInvalidType()
    {
        $this->expectException(Exception\InvalidBumpTypeException::class);
        new BumpCommand('invalid-type', $this->dispatcher->reveal());
    }

    public function expectedTypes() : iterable
    {
        yield 'BUMP_MAJOR'  => [BumpCommand::BUMP_MAJOR, 'bumpMajorVersion'];
        yield 'BUMP_MINOR'  => [BumpCommand::BUMP_MINOR, 'bumpMinorVersion'];
        yield 'BUMP_PATCH'  => [BumpCommand::BUMP_PATCH, 'bumpPatchVersion'];
        yield 'BUMP_BUGFIX' => [BumpCommand::BUMP_BUGFIX, 'bumpPatchVersion'];
    }

    /**
     * @dataProvider expectedTypes
     */
    public function testConstructorAllowsExpectedTypes(string $bumpType)
    {
        $command = new BumpCommand($bumpType, $this->dispatcher->reveal());
        $this->assertInstanceOf(BumpCommand::class, $command);
    }

    /**
     * @dataProvider expectedTypes
     */
    public function testExecutionReturnsZeroOnSuccess(string $bumpType, string $methodName)
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(false);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher, $methodName) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertSame($methodName, $event->bumpMethod());
                TestCase::assertNull($event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpCommand($bumpType, $this->dispatcher->reveal());

        $this->assertSame(0, $this->executeCommand($command));
    }

    /**
     * @dataProvider expectedTypes
     */
    public function testExecutionReturnsOneOnFailure(string $bumpType, string $methodName)
    {
        $input      = $this->input;
        $output     = $this->output;
        $dispatcher = $this->dispatcher;
        $event      = $this->prophesize(BumpChangelogVersionEvent::class);
        $event->failed()->willReturn(true);

        $dispatcher
            ->dispatch(Argument::that(function ($event) use ($input, $output, $dispatcher, $methodName) {
                TestCase::assertSame($input->reveal(), $event->input());
                TestCase::assertSame($output->reveal(), $event->output());
                TestCase::assertSame($dispatcher->reveal(), $event->dispatcher());
                TestCase::assertSame($methodName, $event->bumpMethod());
                TestCase::assertNull($event->version());
                return $event;
            }))
            ->will(function () use ($event) {
                return $event->reveal();
            });

        $command = new BumpCommand($bumpType, $this->dispatcher->reveal());

        $this->assertSame(1, $this->executeCommand($command));
    }
}
