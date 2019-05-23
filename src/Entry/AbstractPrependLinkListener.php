<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

use Phly\KeepAChangelog\Provider\ProviderInterface;

abstract class AbstractPrependLinkListener
{
    abstract public function getIdentifier(AddChangelogEntryEvent $event) : ?int;

    abstract public function generateLink(ProviderInterface $provider, int $identifier) : string;

    abstract public function reportInvalidIdentifier(AddChangelogEntryEvent $event, int $identifier) : void;

    abstract public function reportInvalidLink(AddChangelogEntryEvent $event, string $link) : void;

    public function __invoke(AddChangelogEntryEvent $event) : void
    {
        $identfier = $this->getIdentifier($event);
        if (! $identifier) {
            // Nothing to prepend
            return;
        }

        if (! preg_match('/^[1-9]\d*$/', (string) $identifier)) {
            $this->reportInvalidIdentifier($event, $identifier);
            return;
        }

        $provider = $event->config()->provider();
        if (! $provider->canGenerateLinks()) {
            $event->providerCannotGenerateLinks();
            return;
        }

        $link = $this->generateLink($provider, $identifier);
        if (! $this->probeLink($this->extractUrlFromLink($link))) {
            $this->reportInvalidLink($event, $link);
            return;
        }

        $event->updateEntry(sprintf(
            '%s %s',
            $link,
            $event->entry()
        ));
    }

    protected function extractUrlFromLink(string $link) : string
    {
        if (! preg_match('#\[(?P<url>.*?)\]$#', $link, $matches)) {
            return '';
        }

        return $matches['url'];
    }

    protected function probeLink(string $link) : bool
    {
        if (empty($link)) {
            return false;
        }

        $headers = get_headers(
            $link,
            1,
            stream_context_create(['http' => ['method' => 'HEAD']])
        );
        $statusLine = explode(' ', $headers[0]);
        $statusCode = (int) $statusLine[1];

        if ($statusCode < 300) {
            return true;
        }

        if ($statusCode >= 300
            && $statusCode <= 399
            && array_key_exists('Location', $headers)
        ) {
            return $this->probeLink($headers['Location']);
        }

        return false;
    }
}
