<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\ConfigCommand;

use Phly\KeepAChangelog\ConfigCommand\ShowConfigEvent;
use Phly\KeepAChangelog\ConfigCommand\ShowMergedConfigListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function parse_ini_string;

class ShowMergedConfigListenerTest extends TestCase
{
    private ShowConfigEvent&MockObject $event;

    protected function setUp(): void
    {
        $this->event = $this->createMock(ShowConfigEvent::class);
    }

    public function testListenerReturnsEarlyIfMergedConfigIsNotRequested()
    {
        $this->event->expects($this->atLeastOnce())->method('showMerged')->willReturn(false);
        $this->event->expects($this->never())->method('configIsNotReadable');
        $this->event->expects($this->never())->method('displayMergedConfig');

        $listener = new ShowMergedConfigListener();

        $this->assertNull($listener($this->event));
    }

    public function testListenerReturnsEarlyIfGlobalConfigIsNotReadable()
    {
        $this->event->expects($this->atLeastOnce())->method('showMerged')->willReturn(true);
        $this->event->expects($this->once())->method('configIsNotReadable')->with($this->isType('string'), 'global');
        $this->event->expects($this->never())->method('displayMergedConfig');

        $listener             = new ShowMergedConfigListener();
        $listener->configRoot = __DIR__;

        $this->assertNull($listener($this->event));
    }

    public function testListenerReturnsEarlyIfLocalConfigIsNotReadable()
    {
        $this->event->expects($this->atLeastOnce())->method('showMerged')->willReturn(true);
        $this->event->expects($this->once())->method('configIsNotReadable')->with($this->isType('string'), 'local');
        $this->event->expects($this->never())->method('displayMergedConfig');

        $listener                  = new ShowMergedConfigListener();
        $listener->configRoot      = __DIR__ . '/../_files/config';
        $listener->localConfigRoot = __DIR__;

        $this->assertNull($listener($this->event));
    }

    public function testNotifiesEventToDisplayMergedConfig()
    {
        $this->event->expects($this->atLeastOnce())->method('showMerged')->willReturn(true);
        $this->event->expects($this->never())->method('configIsNotReadable');
        $this->event
            ->expects($this->once())
            ->method('displayMergedConfig')
            ->with($this->callback(function (string $configString): bool {
                $config = parse_ini_string($configString, true);
                TestCase::assertSame('CHANGELOG.txt', $config['defaults']['changelog_file']);
                TestCase::assertSame('github', $config['defaults']['provider']);
                TestCase::assertSame('origin', $config['defaults']['remote']);
                TestCase::assertSame('https://github.mwop.net', $config['providers']['github']['url']);
                return true;
            }));

        $listener                  = new ShowMergedConfigListener();
        $listener->configRoot      = __DIR__ . '/../_files/config';
        $listener->localConfigRoot = __DIR__ . '/../_files/config/local';

        $this->assertNull($listener($this->event));
    }
}
