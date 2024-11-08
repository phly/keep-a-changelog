<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Bump;

use Phly\KeepAChangelog\Bump\BumpChangelogVersionEvent;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BumpChangelogVersionEventTest extends TestCase
{
    private InputInterface&MockObject $input;
    private OutputInterface&MockObject $output;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testInstantiationRaisesExceptionWhenBothBumpMethodAndVersionProvided()
    {
        $this->expectException(Exception\InvalidChangelogBumpCriteriaException::class);
        new BumpChangelogVersionEvent(
            $this->input,
            $this->output,
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
            $this->output,
            $this->dispatcher
        );
    }

    public function testBumpMethodIsAccessibleWhenProvidedDuringInstantiation()
    {
        $event = new BumpChangelogVersionEvent(
            $this->input,
            $this->output,
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
            $this->output,
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
            $this->output,
            $this->dispatcher,
            'bumpMinor'
        );

        $this->output->expects($this->once())->method('writeln')->with($this->stringContains('Bumped changelog'));

        $this->assertNull($event->bumpedChangelog('1.2.3'));
    }

    public function testWhenBumpVersionEqualsUnreleasedConstantVersionIsSetToUnreleasedConstant(): void
    {
        $event = new BumpChangelogVersionEvent(
            $this->input,
            $this->output,
            $this->dispatcher,
            BumpChangelogVersionEvent::UNRELEASED
        );

        $this->assertSame(BumpChangelogVersionEvent::UNRELEASED, $event->version());
        $this->assertNull($event->bumpMethod());
    }
}
