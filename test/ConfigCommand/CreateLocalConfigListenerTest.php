<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\AbstractCreateConfigListener;
use Phly\KeepAChangelog\ConfigCommand\CreateLocalConfigListener;
use Prophecy\Prophecy\ObjectProphecy;

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

    public function configureEventToCreate(ObjectProphecy $event): void
    {
        $event->createLocal()->willReturn(true);
    }

    public function configureEventToSkipCreate(ObjectProphecy $event): void
    {
        $event->createLocal()->willReturn(false);
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
