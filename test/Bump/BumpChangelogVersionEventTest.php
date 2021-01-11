<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpChangelogVersionEventTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->input      = $this->prophesize(InputInterface::class)->reveal();
        $this->output     = $this->prophesize(OutputInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class)->reveal();
    }

    public function testInstantiationRaisesExceptionWhenBothBumpMethodAndVersionProvided()
    {
        $this->expectException(Exception\InvalidChangelogBumpCriteriaException::class);
        new BumpChangelogVersionEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            'bumpMinor',
            '1.2.3'
        );
    }

    public function testInstantiationRaisesExceptionWhenNeitherBumpMethodNorVersionProvided()
    {
        $this->expectException(Exception\InvalidChangelogBumpCriteriaException::class);
        new BumpChangelogVersionEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher
        );
    }

    public function testBumpMethodIsAccessibleWhenProvidedDuringInstantiation()
    {
        $event = new BumpChangelogVersionEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            'bumpMinor'
        );

        $this->assertSame('bumpMinor', $event->bumpMethod());
        $this->assertNull($event->version());
    }

    public function testVersionIsAccessibleWhenProvidedDuringInstantiation()
    {
        $event = new BumpChangelogVersionEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            null,
            '1.2.3'
        );

        $this->assertNull($event->bumpMethod());
        $this->assertSame('1.2.3', $event->version());
    }

    public function testBumpingChangelogEmitsOutput()
    {
        $event = new BumpChangelogVersionEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            'bumpMinor'
        );
        $this->output->writeln(Argument::containingString('Bumped changelog'))->shouldBeCalled();

        $this->assertNull($event->bumpedChangelog('1.2.3'));
    }

    public function testWhenBumpVersionEqualsUnreleasedConstantVersionIsSetToUnreleasedConstant(): void
    {
        $event = new BumpChangelogVersionEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            BumpChangelogVersionEvent::UNRELEASED
        );

        $this->assertSame(BumpChangelogVersionEvent::UNRELEASED, $event->version());
        $this->assertNull($event->bumpMethod());
    }
}
