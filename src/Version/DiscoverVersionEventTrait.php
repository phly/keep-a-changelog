<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

trait DiscoverVersionEventTrait
{
    public function foundVersion(string $version): void
    {
        $this->version = $version;
        $this->tagName = $this->tagName ?: $version;
    }

    public function versionNotAccepted(): void
    {
        $this->failed = true;
        $this->output->writeln('<error>No version specified</error>');
        $this->output->writeln('Please specify a version via the <version> argument.');
    }
}
