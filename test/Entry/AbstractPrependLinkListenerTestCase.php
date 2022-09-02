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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use function sprintf;

abstract class AbstractPrependLinkListenerTestCase extends TestCase
{
    use ProphecyTrait;

    /** @var Config|ObjectProphecy */
    protected $config;

    /** @var null|int */
    protected $identifier;

    /** @var null|string */
    protected $link;

    /** @var ProviderInterface|ObjectProphecy */
    protected $provider;

    /** @var callable */
    protected $voidReturn;

    abstract public function getListener(): AbstractPrependLinkListener;

    /**
     * Setup mock for retrieving empty patch|issue identifier
     */
    abstract public function emptyIdentifierRequested(ObjectProphecy $event): void;

    /**
     * Setup mock for retrieving invalid patch|issue identifier
     */
    abstract public function invalidIdentifierRequested(ObjectProphecy $event): void;

    /**
     * Setup mock for retrieving patch|issue identifier.
     *
     * Should set $identifier.
     */
    abstract public function identifierRequested(ObjectProphecy $event): void;

    /**
     * Setup mock for reporting invalid patch|issue identifier
     */
    abstract public function reportInvalidIdentifierRequested(ObjectProphecy $event): void;

    /**
     * Setup mock for generating an empty patch|issue link
     */
    abstract public function generateEmptyLinkRequested(ObjectProphecy $provider): void;

    /**
     * Setup mock for generating a patch|issue link
     *
     * Should set $link.
     */
    abstract public function generateLinkRequested(ObjectProphecy $provider): void;

    /**
     * Setup mock for reporting invalid patch|issue link
     */
    abstract public function reportInvalidLinkRequested(ObjectProphecy $event): void;

    protected function setUp(): void
    {
        $this->identifier   = null;
        $this->link         = null;
        $this->provider     = $this->prophesize(ProviderInterface::class);
        $this->providerSpec = $this->prophesize(ProviderSpec::class);
        $this->config       = $this->prophesize(Config::class);

        $this->providerSpec->createProvider()->will([$this->provider, 'reveal']);
        $this->config->provider()->will([$this->providerSpec, 'reveal']);

        $this->voidReturn = function () {
        };
    }

    public function getEvent(): ObjectProphecy
    {
        $event = $this->prophesize(AddChangelogEntryEvent::class);
        $event->config()->will([$this->config, 'reveal']);
        $event->entry()->willReturn('This is the entry');
        $event->updateEntry(Argument::type('string'))->will($this->voidReturn);
        $event->providerCannotGenerateLinks()->will($this->voidReturn);
        return $event;
    }

    public function testEmptyIdentifierResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->emptyIdentifierRequested($event);
        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));
        $this->config->provider()->shouldNotHaveBeenCalled();
        $event->updateEntry(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testInvalidIdentifierResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->invalidIdentifierRequested($event);
        $this->reportInvalidIdentifierRequested($event);

        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));
        $this->config->provider()->shouldNotHaveBeenCalled();
        $event->updateEntry(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testProviderThatCannotGenerateLinksResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->identifierRequested($event);

        $this->provider->canGenerateLinks()->willReturn(false);

        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));
        $this->config->provider()->shouldHaveBeenCalled();
        $event->providerCannotGenerateLinks()->shouldHaveBeenCalled();
        $event->updateEntry(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testEmptyLinkGeneratedByProviderResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->identifierRequested($event);
        $this->reportInvalidLinkRequested($event);

        $this->provider->canGenerateLinks()->willReturn(true);
        $this->generateEmptyLinkRequested($this->provider);

        $listener = $this->getListener();

        $this->assertNull($listener($event->reveal()));
        $this->config->provider()->shouldHaveBeenCalled();
        $event->providerCannotGenerateLinks()->shouldNotHaveBeenCalled();
        $event->updateEntry(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testNonProbableLinkGeneratedByProviderResultsInEarlyReturn()
    {
        $event = $this->getEvent();
        $this->identifierRequested($event);

        $this->provider->canGenerateLinks()->willReturn(true);

        $this->generateLinkRequested($this->provider);
        $this->reportInvalidLinkRequested($event);

        $listener                  = $this->getListener();
        $listener->probeLinkStatus = false;

        $this->assertNull($listener($event->reveal()));
        $this->config->provider()->shouldHaveBeenCalled();
        $event->providerCannotGenerateLinks()->shouldNotHaveBeenCalled();
        $event->updateEntry(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testUpdatesEntryInEventWhenComplete()
    {
        $event = $this->getEvent();
        $this->provider->canGenerateLinks()->willReturn(true);

        $this->identifierRequested($event);
        $this->generateLinkRequested($this->provider);

        $listener                  = $this->getListener();
        $listener->probeLinkStatus = true;

        $this->assertNull($listener($event->reveal()));
        $this->config->provider()->shouldHaveBeenCalled();
        $event->providerCannotGenerateLinks()->shouldNotHaveBeenCalled();
        $event
            ->updateEntry(sprintf('%s This is the entry', $this->link))
            ->shouldHaveBeenCalled();
    }
}
