<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Provider\ProviderInterface;

class PrependIssueLinkListener extends AbstractPrependLinkListener
{
    public function getIdentifier(AddChangelogEntryEvent $event): ?int
    {
        return $event->issueNumber();
    }

    public function generateLink(ProviderInterface $provider, int $identifier): string
    {
        return $provider->generateIssueLink($identifier);
    }

    public function reportInvalidIdentifier(AddChangelogEntryEvent $event, int $identifier): void
    {
        $event->issueNumberIsInvalid($identifier);
    }

    public function reportInvalidLink(AddChangelogEntryEvent $event, string $link): void
    {
        $event->issueLinkIsInvalid($link);
    }
}
