<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Changelog;

use Phly\KeepAChangelog\Changelog\CreateNewChangelogEvent;
use Phly\KeepAChangelog\Common;
use Phly\KeepAChangelog\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateNewChangelogEventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->config     = $this->prophesize(Config::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $this->input      = $this->prophesize(InputInterface::class)->reveal();
        $this->output     = $this->prophesize(OutputInterface::class);
    }

    public function testPropagationIsNotStoppedByDefault(): CreateNewChangelogEvent
    {
        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '1.2.3',
            false
        );
        $this->assertFalse($event->isPropagationStopped());

        return $event;
    }

    /**
     * @depends testPropagationIsNotStoppedByDefault
     */
    public function testImplementsVersionAwareInterface(CreateNewChangelogEvent $event)
    {
        $this->assertInstanceOf(Common\VersionAwareEventInterface::class, $event);
    }

    public function booleanFlags(): iterable
    {
        yield 'true' => [true];
        yield 'false' => [false];
    }

    /**
     * @dataProvider booleanFlags
     */
    public function testOverwriteIsBasedOnConstructorArgument(bool $overwrite)
    {
        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '1.2.3',
            $overwrite
        );
        $this->assertSame($overwrite, $event->overwrite());
    }

    public function testNotifyingChangelogExistsStopsPropagationWithFailure()
    {
        $this->config->changelogFile()->willReturn('CHANGELOG.md');

        $this->output
            ->writeln(Argument::containingString('file exists'))
            ->shouldBeCalled();
        $this->output
            ->writeln(Argument::containingString('use the --overwrite|-o option'))
            ->shouldBeCalled();

        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '1.2.3',
            false
        );
        $event->discoveredConfiguration($this->config->reveal());

        $this->assertNull($event->changelogExists());
        $this->assertTrue($event->isPropagationStopped());
        $this->assertTrue($event->failed());
    }

    public function testNotifyingChangelogCreatedEmitsOutput()
    {
        $this->config->changelogFile()->willReturn('CHANGELOG.md');

        $this->output
            ->writeln(Argument::containingString(
                'new changelog in file "CHANGELOG.md" using initial version "1.2.3"'
            ))
            ->shouldBeCalled();

        $event = new CreateNewChangelogEvent(
            $this->input,
            $this->output->reveal(),
            $this->dispatcher,
            '1.2.3',
            false
        );
        $event->discoveredConfiguration($this->config->reveal());

        $event->createdChangelog();
    }
}
