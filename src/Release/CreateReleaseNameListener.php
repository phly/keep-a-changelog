<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

class CreateReleaseNameListener
{
    public function __invoke(CreateReleaseEvent $event) : void
    {
        $name = $event->input()->getOption('name');
        if ($name) {
            $event->setReleaseName($name);
            return;
        }

        $package = $event->package();
        $version = $event->version();
        $lastSeparator = strrpos($package, '/');
        $repo = substr($package, $lastSeparator + 1);
        $event->setReleaseName(sprintf('%s %s', $repo, $version));
    }
}
