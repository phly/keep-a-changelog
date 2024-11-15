<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\AbstractMilestoneProviderEvent;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractMilestoneProviderEventTest extends TestCase
{
    private AbstractMilestoneProviderEvent $event;
    private OutputInterface&MockObject $output;

    public function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->event  = new class () extends AbstractMilestoneProviderEvent {
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
        /** @var ProviderInterface&MockObject $provider */
        $provider = $this->createMock(ProviderInterface::class);
        $this->event->discoveredProvider($provider);
        $this->assertSame($provider, $this->event->provider());
    }

    public function testMarkingIncompleteProviderFailsEvent(): void
    {
        $this->event->setOutput($this->output);
        $this->output->expects($this->atLeastOnce())->method('writeln')->with($this->isType('string'));

        $this->assertNull($this->event->providerIsIncomplete());
        $this->assertTrue($this->event->failed());
    }

    public function testMarkingProviderIncapableOfMilestonesFailsEvent(): void
    {
        $this->event->setOutput($this->output);
        $this->output->expects($this->atLeastOnce())->method('writeln')->with($this->isType('string'));

        $this->assertNull($this->event->providerIncapableOfMilestones());
        $this->assertTrue($this->event->failed());
    }
}
