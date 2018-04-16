<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\AddEntry;
use Phly\KeepAChangelog\EntryCommand;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputInterface;
use TypeError;

class EntryCommandTest extends TestCase
{
    public function setUp()
    {
        $this->input = $this->prophesize(InputInterface::class);
    }

    public function reflectMethod(EntryCommand $command, string $method) : ReflectionMethod
    {
        $r = new ReflectionMethod($command, $method);
        $r->setAccessible(true);
        return $r;
    }

    public function testConstructorRequiresAName()
    {
        $this->expectException(TypeError::class);
        new EntryCommand();
    }

    public function nonNamespacedCommandNames()
    {
        return [
            'invalid' => ['invalid'],
            'known-type-standalone' => [AddEntry::TYPE_ADDED],
        ];
    }

    /**
     * @dataProvider nonNamespacedCommandNames
     */
    public function testConstructorRaisesExceptionForNonNamespacedCommandNames(?string $name)
    {
        $this->expectException(Exception\InvalidNoteTypeException::class);
        new EntryCommand($name);
    }

    public function testConstructorRaisesExceptionWhenNamespacedCommandDoesNotEndInValidType()
    {
        $this->expectException(Exception\InvalidNoteTypeException::class);
        new EntryCommand('command:invalid');
    }

    public function testPrepareEntryRaisesExceptionForEmptyEntry()
    {
        $this->input->getArgument('entry')->willReturn(null);
        $command = new EntryCommand('entry:added');

        $this->expectException(Exception\EmptyEntryException::class);
        $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal());
    }

    public function testPrepareEntryReturnsEntryVerbatimIfNoPrOptionProvided()
    {
        $entry = 'This is the entry';
        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn(null);

        $command = new EntryCommand('entry:added');

        $this->assertSame(
            $entry,
            $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal())
        );
    }

    public function testPrepareEntryWithPrOptionRaisesExceptionIfPackageOptionIsInvalid()
    {
        $entry = 'This is the entry';
        $pr = 42;
        $package = 'not-a-valid-package-name';

        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn($pr);
        $this->input->getOption('package')->willReturn($package);

        $command = new EntryCommand('entry:added');

        $this->expectException(Exception\InvalidPackageNameException::class);
        $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal());
    }

    public function testPrepareEntryReturnsEntryWithPrLinkPrefixedWhenPackageOptionPresentAndValid()
    {
        $entry = 'This is the entry';
        $pr = 42;
        $package = 'phly/keep-a-changelog';

        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn($pr);
        $this->input->getOption('package')->willReturn($package);

        $command = new EntryCommand('entry:added');

        $expected = '[#42](https://github.com/phly/keep-a-changelog/pull/42) ' . $entry;

        $this->assertSame(
            $expected,
            $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal())
        );
    }
}
