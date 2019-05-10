<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\ConfigCommand;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigCommandTest extends TestCase
{
    public function setUp()
    {
        vfsStream::setup('config');
        $rootPath = vfsStream::url('config');
        $this->globalPath = sprintf('%s/global', $rootPath);
        $this->localPath  = sprintf('%s/local', $rootPath);

        mkdir($this->globalPath, 0777, true);
        mkdir($this->localPath, 0777, true);
    }

    public function tearDown()
    {
        $globalConfig = sprintf('%s/.keep-a-changelog/config.ini', $this->globalPath);
        if (file_exists($globalConfig)) {
            unlink($globalConfig);
        }

        $localConfig  = sprintf('%s/.keep-a-changelog.ini', $this->localPath);
        if (file_exists($localConfig)) {
            unlink($localConfig);
        }
    }

    public function testExecutionRaisesExceptionWhenLocalFileExistsAndOverwriteNotRequested()
    {
        $configFile = sprintf('%s/.keep-a-changelog.ini', $this->localPath);
        file_put_contents($configFile, 'provider = github');

        $input = $this->prophesize(InputInterface::class);
        $input->getOption('overwrite')->willReturn(null);
        $input->getOption('global')->willReturn(null);

        $output = $this->prophesize(OutputInterface::class);

        $command = new ConfigCommand();
        $this->setCommandProperty($command, 'localPath', $this->localPath);

        $this->expectException(Exception\ConfigFileExistsException::class);
        $this->reflectMethod($command, 'execute')->invoke($command, $input->reveal(), $output->reveal());
    }

    public function testExecutionRaisesExceptionWhenGlobalFileExistsAndOverwriteNotRequested()
    {
        mkdir($this->globalPath . '/.keep-a-changelog', 0777, true);
        $configFile = sprintf('%s/.keep-a-changelog/config.ini', $this->globalPath);
        file_put_contents($configFile, 'provider = github');

        $input = $this->prophesize(InputInterface::class);
        $input->getOption('overwrite')->willReturn(null);
        $input->getOption('global')->willReturn(true);

        $output = $this->prophesize(OutputInterface::class);

        $command = new ConfigCommand();
        $this->setCommandProperty($command, 'globalPath', $this->globalPath);

        $this->expectException(Exception\ConfigFileExistsException::class);
        $this->reflectMethod($command, 'execute')->invoke($command, $input->reveal(), $output->reveal());
    }

    public function testExecutionCanCreateLocalConfigFile()
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption('overwrite')->willReturn(null);
        $input->getOption('global')->willReturn(null);

        $output = $this->prophesize(OutputInterface::class);
        $output
            ->writeln(Argument::containingString('Created config file'))
            ->shouldBeCalled();

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(ChoiceQuestion::class)
            )
            ->willReturn(Config::PROVIDER_GITLAB);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(Question::class)
            )
            ->willReturn('this-is-the-token');

        $command = new ConfigCommand();
        $this->setCommandProperty($command, 'localPath', $this->localPath);
        $this->setCommandProperty($command, 'questionHelper', $questionHelper->reveal());

        $this->assertSame(
            0,
            $this->reflectMethod($command, 'execute')->invoke($command, $input->reveal(), $output->reveal())
        );

        $file = sprintf('%s/.keep-a-changelog.ini', $this->localPath);
        $this->assertFileExists($file);
        $contents = file_get_contents($file);
        $this->assertRegExp('/token = this-is-the-token/m', $contents);
        $this->assertRegExp('/provider = gitlab/m', $contents);
    }

    public function testExecutionCanCreateGlobalConfigFile()
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption('overwrite')->willReturn(null);
        $input->getOption('global')->willReturn(true);

        $output = $this->prophesize(OutputInterface::class);
        $output
            ->writeln(Argument::containingString('Created config file'))
            ->shouldBeCalled();

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(ChoiceQuestion::class)
            )
            ->willReturn(Config::PROVIDER_GITLAB);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(Question::class)
            )
            ->willReturn('this-is-the-token');

        $command = new ConfigCommand();
        $this->setCommandProperty($command, 'globalPath', $this->globalPath);
        $this->setCommandProperty($command, 'questionHelper', $questionHelper->reveal());

        $this->assertSame(
            0,
            $this->reflectMethod($command, 'execute')->invoke($command, $input->reveal(), $output->reveal())
        );

        $file = sprintf('%s/.keep-a-changelog/config.ini', $this->globalPath);
        $this->assertFileExists($file);
        $contents = file_get_contents($file);
        $this->assertRegExp('/token = this-is-the-token/m', $contents);
        $this->assertRegExp('/provider = gitlab/m', $contents);
    }

    public function testExecutionCanOverwriteLocalConfigFile()
    {
        $file = sprintf('%s/.keep-a-changelog.ini', $this->localPath);
        file_put_contents($file, "token = original-token\nprovider = github");

        $input = $this->prophesize(InputInterface::class);
        $input->getOption('overwrite')->willReturn(true);
        $input->getOption('global')->willReturn(null);

        $output = $this->prophesize(OutputInterface::class);
        $output
            ->writeln(Argument::containingString('Created config file'))
            ->shouldBeCalled();

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(ChoiceQuestion::class)
            )
            ->willReturn(Config::PROVIDER_GITLAB);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(Question::class)
            )
            ->willReturn('this-is-the-token');

        $command = new ConfigCommand();
        $this->setCommandProperty($command, 'localPath', $this->localPath);
        $this->setCommandProperty($command, 'questionHelper', $questionHelper->reveal());

        $this->assertSame(
            0,
            $this->reflectMethod($command, 'execute')->invoke($command, $input->reveal(), $output->reveal())
        );

        $contents = file_get_contents($file);
        $this->assertRegExp('/token = this-is-the-token/m', $contents);
        $this->assertRegExp('/provider = gitlab/m', $contents);
    }

    public function testExecutionCanOverwriteGlobalConfigFile()
    {
        mkdir($this->globalPath . '/.keep-a-changelog', 0777, true);
        $file = sprintf('%s/.keep-a-changelog/config.ini', $this->globalPath);
        file_put_contents($file, "token = original-token\nprovider = github");

        $input = $this->prophesize(InputInterface::class);
        $input->getOption('overwrite')->willReturn(true);
        $input->getOption('global')->willReturn(true);

        $output = $this->prophesize(OutputInterface::class);
        $output
            ->writeln(Argument::containingString('Created config file'))
            ->shouldBeCalled();

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(ChoiceQuestion::class)
            )
            ->willReturn(Config::PROVIDER_GITLAB);
        $questionHelper
            ->ask(
                Argument::that([$input, 'reveal']),
                Argument::that([$output, 'reveal']),
                Argument::type(Question::class)
            )
            ->willReturn('this-is-the-token');

        $command = new ConfigCommand();
        $this->setCommandProperty($command, 'globalPath', $this->globalPath);
        $this->setCommandProperty($command, 'questionHelper', $questionHelper->reveal());

        $this->assertSame(
            0,
            $this->reflectMethod($command, 'execute')->invoke($command, $input->reveal(), $output->reveal())
        );

        $contents = file_get_contents($file);
        $this->assertRegExp('/token = this-is-the-token/m', $contents);
        $this->assertRegExp('/provider = gitlab/m', $contents);
    }

    private function reflectMethod(ConfigCommand $command, string $method) : ReflectionMethod
    {
        $r = new ReflectionMethod($command, $method);
        $r->setAccessible(true);
        return $r;
    }

    private function setCommandProperty(ConfigCommand $command, string $property, $value) : void
    {
        $r = new ReflectionProperty($command, $property);
        $r->setAccessible(true);
        $r->setValue($command, $value);
    }
}
