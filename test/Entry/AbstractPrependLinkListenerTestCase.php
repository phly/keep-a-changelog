<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Entry\AbstractPrependLinkListener;
use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderSpec;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function sprintf;

abstract class AbstractPrependLinkListenerTestCase extends TestCase
{
    protected Config&MockObject $config;
    protected null|int $identifier;
    protected null|string $link;
    protected ProviderInterface&MockObject $provider;
    protected providerSpec&MockObject $providerSpec;

    abstract public function getListener(): AbstractPrependLinkListener;

    /**
     * Setup mock for retrieving empty patch|issue identifier
     */
    abstract public function emptyIdentifierRequested(AddChangelogEntryEvent&MockObject $event): void;

    /**
     * Setup mock for retrieving invalid patch|issue identifier
     */
    abstract public function invalidIdentifierRequested(AddChangelogEntryEvent&MockObject $event): void;

    /**
     * Setup mock for retrieving patch|issue identifier.
     *
     * Should set $identifier.
     */
    abstract public function identifierRequested(AddChangelogEntryEvent&MockObject $event): void;

    /**
     * Setup mock for reporting invalid patch|issue identifier
     */
    abstract public function reportInvalidIdentifierRequested(AddChangelogEntryEvent&MockObject $event): void;

    /**
     * Setup mock for generating an empty patch|issue link
     */
    abstract public function generateEmptyLinkRequested(ProviderInterface&MockObject $provider): void;

    /**
     * Setup mock for generating a patch|issue link
     *
     * Should set $link.
     */
    abstract public function generateLinkRequested(ProviderInterface&MockObject $provider): void;

    /**
     * Setup mock for reporting invalid patch|issue link
     */
    abstract public function reportInvalidLinkRequested(AddChangelogEntryEvent&MockObject $event): void;

    protected function setUp(): void
    {
        $this->identifier   = null;
        $this->link         = null;
        $this->provider     = $this->createMock(ProviderInterface::class);
        $this->providerSpec = $this->createMock(ProviderSpec::class);
        $this->config       = $this->createMock(Config::class);

        $this->providerSpec->expects($this->any())->method('createProvider')->willReturn($this->provider);
        $this->config->expects($this->any())->method('provider')->willReturn($this->providerSpec);
    }

    public function getEvent(): AddChangelogEntryEvent&MockObject
    {
        /** @var AddChangelogEntryEvent&MockObject $event */
        $event = $this->createMock(AddChangelogEntryEvent::class);

        $event->expects($this->any())->method('config')->willReturn($this->config);
        $event->expects($this->any())->method('entry')->willReturn('This is the entry');

        return $event;
    }

    public function testEmptyIdentifierResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->emptyIdentifierRequested($event);
        $listener = $this->getListener();

        $this->config->expects($this->never())->method('provider');
        $event->expects($this->never())->method('updateEntry');

        $this->assertNull($listener($event));
    }

    public function testInvalidIdentifierResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->invalidIdentifierRequested($event);
        $this->reportInvalidIdentifierRequested($event);

        $this->config->expects($this->never())->method('provider');
        $event->expects($this->never())->method('updateEntry');

        $listener = $this->getListener();

        $this->assertNull($listener($event));
    }

    public function testProviderThatCannotGenerateLinksResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->identifierRequested($event);

        $this->config->expects($this->atLeastOnce())->method('provider');
        $event->expects($this->atLeastOnce())->method('providerCannotGenerateLinks');
        $event->expects($this->never())->method('updateEntry');
        $this->provider->expects($this->once())->method('canGenerateLinks')->willReturn(false);

        $listener = $this->getListener();

        $this->assertNull($listener($event));
    }

    public function testEmptyLinkGeneratedByProviderResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->identifierRequested($event);
        $this->reportInvalidLinkRequested($event);

        $this->config->expects($this->atLeastOnce())->method('provider');
        $event->expects($this->never())->method('providerCannotGenerateLinks');
        $event->expects($this->never())->method('updateEntry');
        $this->provider->expects($this->once())->method('canGenerateLinks')->willReturn(true);
        $this->generateEmptyLinkRequested($this->provider);

        $listener = $this->getListener();

        $this->assertNull($listener($event));
    }

    public function testNonProbableLinkGeneratedByProviderResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->identifierRequested($event);

        $this->config->expects($this->atLeastOnce())->method('provider');
        $event->expects($this->never())->method('providerCannotGenerateLinks');
        $event->expects($this->never())->method('updateEntry');
        $this->provider->expects($this->once())->method('canGenerateLinks')->willReturn(true);

        $this->generateLinkRequested($this->provider);
        $this->reportInvalidLinkRequested($event);

        $listener                  = $this->getListener();
        $listener->probeLinkStatus = false;

        $this->assertNull($listener($event));
    }

    public function testUpdatesEntryInEventWhenComplete()
    {
        $event = $this->getEvent();

        $this->identifierRequested($event);
        $this->generateLinkRequested($this->provider);

        $this->config->expects($this->atLeastOnce())->method('provider');
        $event->expects($this->never())->method('providerCannotGenerateLinks');
        $event->expects($this->once())->method('updateEntry')->with(sprintf('%s This is the entry', $this->link));
        $this->provider->expects($this->once())->method('canGenerateLinks')->willReturn(true);

        $listener                  = $this->getListener();
        $listener->probeLinkStatus = true;

        $this->assertNull($listener($event));
    }
}
