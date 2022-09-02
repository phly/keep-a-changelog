<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\AbstractMilestoneProviderEvent;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractMilestoneProviderEventTest extends TestCase
{
    use ProphecyTrait;

    /** @var AbstractMilestoneProviderEvent */
    private $event;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    public function setUp(): void
    {
        $this->output = $this->prophesize(OutputInterface::class);
        $this->output->writeln(Argument::type('string'))->willReturn(null);
        $this->event = new class () extends AbstractMilestoneProviderEvent {
            public function isPropagationStopped(): bool
            {
                return $this->failed();
            }

            public function setOutput(OutputInterface $output): void
            {
                $this->output = $output;
            }
        };
    }

    public function testProviderIsNullByDefault(): void
    {
        $this->assertNull($this->event->provider());
    }

    public function testDiscoveringProviderMakesItAccessible(): void
    {
        $provider = $this->prophesize(ProviderInterface::class)->reveal();
        $this->event->discoveredProvider($provider);
        $this->assertSame($provider, $this->event->provider());
    }

    public function testMarkingIncompleteProviderFailsEvent(): void
    {
        $this->event->setOutput($this->output->reveal());

        $this->assertNull($this->event->providerIsIncomplete());
        $this->output->writeln(Argument::type('string'))->shouldHaveBeenCalled();
        $this->assertTrue($this->event->failed());
    }

    public function testMarkingProviderIncapableOfMilestonesFailsEvent(): void
    {
        $this->event->setOutput($this->output->reveal());

        $this->assertNull($this->event->providerIncapableOfMilestones());
        $this->output->writeln(Argument::type('string'))->shouldHaveBeenCalled();
        $this->assertTrue($this->event->failed());
    }
}
