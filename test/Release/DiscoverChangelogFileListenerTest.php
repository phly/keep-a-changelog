<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Release;

use Phly\KeepAChangelog\Release\DiscoverChangelogFileListener;
use Phly\KeepAChangelog\Release\PrepareChangelogEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class DiscoverChangelogFileListenerTest extends TestCase
{
    public function setUp()
    {
        $this->input  = $this->prophesize(InputInterface::class);
        $this->event  = $this->prophesize(PrepareChangelogEvent::class);

        $this->event->input()->will([$this->input, 'reveal']);
    }

    public function testSetsEventChangelogFileUsingProjectChangelogWhenNoOptionPresent()
    {
        $expected = realpath(getcwd()) . '/CHANGELOG.md';
        $listener = new DiscoverChangelogFileListener();
        $this->input->getOption('file')->willReturn(null);
        $this->event->setChangelogFile($expected)->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testSetsEventChangelogFileUsingProvidedOptionWhenReadable()
    {
        $expected = realpath(__DIR__ . '/../_files') . '/CHANGELOG.md';
        $listener = new DiscoverChangelogFileListener();
        $this->input->getOption('file')->willReturn($expected);
        $this->event->setChangelogFile($expected)->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testTellsEventChangelogFileIsUnreadableIfProvidedFileIsNotReadable()
    {
        $expected = realpath(__DIR__) . '/CHANGELOG.md';
        $listener = new DiscoverChangelogFileListener();
        $this->input->getOption('file')->willReturn($expected);
        $this->event->changelogFileIsUnreadable($expected)->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }
}
