<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use org\bovigo\vfs\vfsStream;
use Phly\KeepAChangelog\AddEntry;
use Phly\KeepAChangelog\EntryCommand;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Console\Input\InputInterface;
use TypeError;

use function mkdir;
use function sprintf;

class EntryCommandTest extends TestCase
{
    public function setUp()
    {
        $this->input = $this->prophesize(InputInterface::class);

        vfsStream::setup('entry');
        $rootPath = vfsStream::url('config');
        $this->globalPath  = sprintf('%s/global', $rootPath);
        $this->localPath  = sprintf('%s/local', $rootPath);
        mkdir($this->globalPath, 0777, true);
        mkdir($this->localPath, 0777, true);
    }

    public function testConstructorRequiresAName()
    {
        $this->expectException(TypeError::class);
        new EntryCommand();
    }

    public function nonNamespacedCommandNames() : iterable
    {
        // @phpcs:disable
        return [
            'invalid'               => ['invalid'],
            'known-type-standalone' => [AddEntry::TYPE_ADDED],
        ];
        // @phpcs:enable
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
        $this->injectCommandConfigPaths($command);

        $this->expectException(Exception\EmptyEntryException::class);
        $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal());
    }

    public function testPrepareEntryReturnsEntryVerbatimIfNoPrOptionProvided()
    {
        $entry = 'This is the entry';
        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn(null);

        $command = new EntryCommand('entry:added');
        $this->injectCommandConfigPaths($command);

        $this->assertSame(
            $entry,
            $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal())
        );
    }

    public function testPrepareEntryWithPrOptionRaisesExceptionIfPackageOptionIsInvalid()
    {
        $entry = 'This is the entry';
        $pr = 1;
        $package = 'not-a-valid-package-name';
        $provider = '';

        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn($pr);
        $this->input->getOption('package')->willReturn($package);
        $this->input->getOption('global')->willReturn(null);
        $this->input->hasOption('token')->willReturn(false);
        $this->input->getOption('token')->shouldNotBeCalled();
        $this->input->getOption('provider')->willReturn($provider);
        $this->input->getOption('provider-domain')->willReturn('');

        $command = new EntryCommand('entry:added');
        $this->injectCommandConfigPaths($command);

        $this->expectException(Exception\InvalidPackageNameException::class);
        $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal());
    }

    public function testPrepareEntryWithPrOptionRaisesExceptionIfLinkIsInvalid()
    {
        $entry = 'This is the entry';
        $pr = 9999999999;
        $package = 'phly/keep-a-changelog';
        $provider = '';

        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn($pr);
        $this->input->getOption('package')->willReturn($package);
        $this->input->getOption('global')->willReturn(null);
        $this->input->hasOption('token')->willReturn(false);
        $this->input->getOption('token')->shouldNotBeCalled();
        $this->input->getOption('provider')->willReturn($provider);
        $this->input->getOption('provider-domain')->willReturn('');

        $command = new EntryCommand('entry:added');
        $this->injectCommandConfigPaths($command);

        $this->expectException(Exception\InvalidPullRequestLinkException::class);
        $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal());
    }

    public function testPrepareEntryReturnsEntryWithPrLinkPrefixedWhenPackageOptionPresentAndValid()
    {
        $entry = 'This is the entry';
        $pr = 1;
        $package = 'phly/keep-a-changelog';
        $provider = '';

        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn($pr);
        $this->input->getOption('package')->willReturn($package);
        $this->input->getOption('global')->willReturn(null);
        $this->input->hasOption('token')->willReturn(false);
        $this->input->getOption('token')->shouldNotBeCalled();
        $this->input->getOption('provider')->willReturn($provider);
        $this->input->getOption('provider-domain')->willReturn('');

        $command = new EntryCommand('entry:added');
        $this->injectCommandConfigPaths($command);

        $expected = '[#1](https://github.com/phly/keep-a-changelog/pull/1) ' . $entry;

        $this->assertSame(
            $expected,
            $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal())
        );
    }

    public function testPrepareEntryReturnsEntryWithGitLabPrLinkPrefixedWhenPackageOptionPresentAndValid()
    {
        $entry = 'This is the entry';
        $pr = 1;
        $package = 'phly/keep-a-changelog';
        $provider = 'gitlab';

        $this->input->getArgument('entry')->willReturn($entry);
        $this->input->getOption('pr')->willReturn($pr);
        $this->input->getOption('package')->willReturn($package);
        $this->input->getOption('global')->willReturn(null);
        $this->input->hasOption('token')->willReturn(false);
        $this->input->getOption('token')->shouldNotBeCalled();
        $this->input->getOption('provider')->willReturn($provider);
        $this->input->getOption('provider-domain')->willReturn('');

        $command = new EntryCommand('entry:added');
        $this->injectCommandConfigPaths($command);

        $expected = '[!1](https://gitlab.com/phly/keep-a-changelog/merge_requests/1) ' . $entry;

        $this->assertSame(
            $expected,
            $this->reflectMethod($command, 'prepareEntry')->invoke($command, $this->input->reveal())
        );
    }

    public function reflectMethod(EntryCommand $command, string $method) : ReflectionMethod
    {
        $r = new ReflectionMethod($command, $method);
        $r->setAccessible(true);
        return $r;
    }

    /**
     * @param mixed $value
     */
    private function setCommandProperty(EntryCommand $command, string $property, $value) : void
    {
        $r = new ReflectionProperty($command, $property);
        $r->setAccessible(true);
        $r->setValue($command, $value);
    }

    private function injectCommandConfigPaths(EntryCommand $command) : void
    {
        $this->setCommandProperty($command, 'globalPath', $this->globalPath);
        $this->setCommandProperty($command, 'localPath', $this->localPath);
    }
}
