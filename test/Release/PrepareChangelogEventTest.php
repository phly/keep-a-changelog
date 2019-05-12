<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\PrepareChangelogEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\StoppableEventInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareChangelogEventTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);
    }

    public function createEvent() : PrepareChangelogEvent
    {
        return new PrepareChangelogEvent(
            $this->input->reveal(),
            $this->output->reveal(),
            '1.2.3'
        );
    }

    public function testIsAStoppableEvent()
    {
        $event = $this->createEvent();
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
    }

    public function testVersionIsAccessible()
    {
        $event = $this->createEvent();
        $this->assertSame('1.2.3', $event->version());
    }

    public function testChangelogFileIsNotSetByDefault()
    {
        $event = $this->createEvent();
        $this->assertNull($event->changelogFile());
    }

    public function testChangelogIsNotSetByDefault()
    {
        $event = $this->createEvent();
        $this->assertNull($event->changelog());
        return $event;
    }

    public function testPropagationIsNotStoppedByDefault()
    {
        $event = $this->createEvent();
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testMarkingChangelogFileIsUnreadableStopsPropagationAndEmitsOutput()
    {
        $event = $this->createEvent();
        $event->changelogFileIsUnreadable('changelog.md');

        $this->output
            ->writeln(Argument::containingString('Changelog file "changelog.md" is unreadable'))
            ->shouldHaveBeenCalled();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testCanSetChangelogFile()
    {
        $event = $this->createEvent();
        $event->setChangelogFile('changelog.md');
        $this->assertSame('changelog.md', $event->changelogFile());
    }

    public function testIndicatingParsingErrorStopsPropagationAndEmitsOutputWithErrorDetails()
    {
        $error = new RuntimeException('parsing error');
        $event = $this->createEvent();
        $event->setChangelogFile('changelog.md');

        $event->errorParsingChangelog($error);

        $this->output
            ->writeln(Argument::containingString(
                'An error occurred parsing the changelog file "changelog.md" for the release "1.2.3"'
            ))
            ->shouldHaveBeenCalled();
        $this->output
            ->writeln(Argument::containingString($error->getMessage()))
            ->shouldHaveBeenCalled();
    }

    /**
     * @depends testChangelogIsNotSetByDefault
     */
    public function testSettingRawChangelogMakesChangelogAccessible(PrepareChangelogEvent $event)
    {
        $changelog = 'this is the changelog';
        $event->setRawChangelog($changelog);
        $this->assertSame($changelog, $event->changelog());
        return $event;
    }

    /**
     * @depends testChangelogIsNotSetByDefault
     */
    public function testSettingFormattedChangelogMakesChangelogAccessible(PrepareChangelogEvent $event)
    {
        $changelog = 'this is the changelog';
        $event->setFormattedChangelog($changelog);
        $this->assertSame($changelog, $event->changelog());
    }

    /**
     * @depends testSettingRawChangelogMakesChangelogAccessible
     */
    public function testFormattedChangelogTakesPrecedenceOverRawChangelog(PrepareChangelogEvent $event)
    {
        $changelog = 'FORMATTED CHANGELOG';
        $this->assertNotSame($changelog, $event->changelog());

        $event->setFormattedChangelog($changelog);

        $this->assertSame($changelog, $event->changelog());
    }
}
