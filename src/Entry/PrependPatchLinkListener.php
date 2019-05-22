<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Entry;

class PrependPatchLinkListener
{
    public function __invoke(AddChangelogEntryEvent $event) : void
    {
        $event->output()->writeln(sprintf(
            '<info>Preparing entry for %s section</info>',
            ucwords($event->entryType())
        ));

        $patch = $event->patchNumber();
        if (! $patch) {
            // Nothing to prepend
            return;
        }

        if (! preg_match('/^[1-9]\d*$/', (string) $patch)) {
            $event->patchNumberIsInvalid($patch);
            return;
        }

        $provider = $event->config()->provider();
        if (! $provider->canGenerateLinks()) {
            $event->providerCannotGenerateLinks();
            return;
        }

        $link = $provider->generatePatchLink($patch);
        if (! $this->probeLink($this->extractUrlFromLink($link))) {
            $event->patchLinkIsInvalid($link);
            return;
        }

        $this->updateEntry(sprintf(
            '%s %s',
            $link,
            $event->entry()
        ));
    }

    private function extractUrlFromLink(string $link) : string
    {
        if (! preg_match('#\[(?P<url>.*?)\]$#', $link, $matches)) {
            return '';
        }

        return $matches['url'];
    }

    private function probeLink(string $link) : bool
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
