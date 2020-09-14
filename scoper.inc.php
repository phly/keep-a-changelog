<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'whitelist' => [
        // Whitelist providers
        'Phly\KeepAChangelog\Provider\GitHub',
        'Phly\KeepAChangelog\Provider\GitLab',
        'Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface',
        'Phly\KeepAChangelog\Provider\ProviderInterface',
    ],
    // Necessary for allowing polyfill classes
    'whitelist-global-classes' => false,
];
