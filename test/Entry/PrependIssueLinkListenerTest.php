<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Entry\AbstractPrependLinkListener;
use Phly\KeepAChangelog\Entry\AddChangelogEntryEvent; // phpcs:ignore
use Phly\KeepAChangelog\Entry\PrependIssueLinkListener;
use Phly\KeepAChangelog\Provider\ProviderInterface; // phpcs:ignore
use PHPUnit\Framework\MockObject\MockObject;

use function sprintf;

class PrependIssueLinkListenerTest extends AbstractPrependLinkListenerTestCase
{
    public function getListener(): AbstractPrependLinkListener
    {
        return new PrependIssueLinkListener();
    }

    /**
     * Setup mock for retrieving empty patch|issue identifier
     */
    public function emptyIdentifierRequested(AddChangelogEntryEvent&MockObject $event): void
    {
        $event->expects($this->any())->method('issueNumber')->willReturn(null);
    }

    /**
     * Setup mock for retrieving invalid patch|issue identifier
     */
    public function invalidIdentifierRequested(AddChangelogEntryEvent&MockObject $event): void
    {
        $this->identifier = -1;
        $event->expects($this->any())->method('issueNumber')->willReturn($this->identifier);
    }

    /**
     * Setup mock for retrieving patch|issue identifier.
     *
     * Should set $identifier.
     */
    public function identifierRequested(AddChangelogEntryEvent&MockObject $event): void
    {
        $this->identifier = 42;
        $event->expects($this->any())->method('issueNumber')->willReturn($this->identifier);
    }

    /**
     * Setup mock for reporting invalid patch|issue identifier
     */
    public function reportInvalidIdentifierRequested(AddChangelogEntryEvent&MockObject $event): void
    {
        $event
            ->expects($this->atLeastOnce())
            ->method('issueNumberIsInvalid')
            ->with($this->identifier);
    }

    /**
     * Setup mock for generating an empty patch|issue link
     */
    public function generateEmptyLinkRequested(ProviderInterface&MockObject $provider): void
    {
        $this->link = '';
        $provider
            ->expects($this->atLeastOnce())
            ->method('generateIssueLink')
            ->with($this->identifier)
            ->willReturn($this->link);
    }

    /**
     * Setup mock for generating a patch|issue link
     *
     * Should set $link.
     */
    public function generateLinkRequested(ProviderInterface&MockObject $provider): void
    {
        $this->link = sprintf('[#%s](https://git.example.org/issue/%s)', $this->identifier, $this->identifier);
        $provider
            ->expects($this->atLeastOnce())
            ->method('generateIssueLink')
            ->with($this->identifier)
            ->willReturn($this->link);
    }

    /**
     * Setup mock for reporting invalid patch|issue link
     */
    public function reportInvalidLinkRequested(AddChangelogEntryEvent&MockObject $event): void
    {
        $event
            ->expects($this->atLeastOnce())
            ->method('issueLinkIsInvalid')
            ->with(empty($this->link) ? '' : $this->stringContains($this->link));
    }
}
