<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Compose this trait for any command that needs access to the repository provider.
 */
trait GetProviderTrait
{
    private $provider = null;

    private function getProvider(InputInterface $input) : ProviderInterface
    {
        if ($this->provider instanceof ProviderInterface) {
            return $this->provider;
        }

        $provider = $input->getOption('provider');

        if ($provider === 'gitlab') {
            $this->provider = new GitLab();
            return $this->provider;
        }

        $this->provider = new GitHub();

        return $this->provider;
    }
}
