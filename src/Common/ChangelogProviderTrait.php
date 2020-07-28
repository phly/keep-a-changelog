<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use Throwable;

use function sprintf;

/**
 * Provides an implementation of ChangelogAwareEventInterface.
 */
trait ChangelogProviderTrait
{
    /** @var null|string */
    private $changelog;

    public function changelog(): ?string
    {
        return $this->changelog;
    }

    public function updateChangelog(string $changelog): void
    {
        $this->changelog = $changelog;
    }

    public function errorParsingChangelog(string $changelogFile, Throwable $e)
    {
        $this->failed = true;
        $output       = $this->output();
        $output->writeln(sprintf(
            '<error>An error occurred parsing the changelog file "%s" for the release "%s":</error>',
            $changelogFile,
            $this->version()
        ));
        $output->writeln($e->getMessage());
    }
}
