<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractCreateConfigListener;
use Phly\KeepAChangelog\ConfigCommand\CreateConfigEvent; // phpcs:ignore
use Phly\KeepAChangelog\ConfigCommand\CreateLocalConfigListener;
use PHPUnit\Framework\MockObject\MockObject;

use function getcwd;
use function sprintf;
use function sys_get_temp_dir;

class CreateLocalConfigListenerTest extends AbstractCreateConfigListenerTestCase
{
    public function getListener(): AbstractCreateConfigListener
    {
        $root                 = sys_get_temp_dir();
        $this->tempConfigFile = sprintf('%s/.keep-a-changelog.ini', $root);

        $listener             = new CreateLocalConfigListener();
        $listener->configRoot = $root;
        return $listener;
    }

    public function getListenerWithExistingFile(): AbstractCreateConfigListener
    {
        $root                     = __DIR__ . '/../_files/config/local';
        $this->existingConfigFile = sprintf('%s/.keep-a-changelog.ini', $root);

        $listener             = new CreateLocalConfigListener();
        $listener->configRoot = $root;
        return $listener;
    }

    public function getListenerToFailCreatingFile(): AbstractCreateConfigListener
    {
        $root                 = '/dev/null';
        $this->tempConfigFile = sprintf('%s/.keep-a-changelog.ini', $root);

        $listener             = new CreateLocalConfigListener();
        $listener->configRoot = $root;
        return $listener;
    }

    public function configureEventToCreate(CreateConfigEvent&MockObject $event): void
    {
        $event->expects($this->atLeastOnce())->method('createLocal')->willReturn(true);
    }

    public function configureEventToSkipCreate(CreateConfigEvent&MockObject $event): void
    {
        $event->expects($this->atLeastOnce())->method('createLocal')->willReturn(false);
    }

    public function testUsesLocalDotfileAsConfigFile()
    {
        $listener = new CreateLocalConfigListener();
        $this->assertSame(getcwd() . '/.keep-a-changelog.ini', $listener->getConfigFileName());
    }

    public function testTemplateDoesNotIncludeTokens()
    {
        $listener = new CreateLocalConfigListener();
        $template = $listener->getConfigTemplate();
        $this->assertDoesNotMatchRegularExpression('/^github\[token\]/m', $template);
        $this->assertDoesNotMatchRegularExpression('/^gitlab\[token\]/m', $template);
    }
}
