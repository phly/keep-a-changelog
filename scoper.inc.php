<?php

declare(strict_types=1);

return [
    'expose-classes' => [
        // Expose providers and provider interfaces
        'Phly\KeepAChangelog\Provider\GitHub',
        'Phly\KeepAChangelog\Provider\GitLab',
        'Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface',
        'Phly\KeepAChangelog\Provider\ProviderInterface',
    ],
    // Necessary for allowing polyfill classes
    'expose-global-classes' => false,
];
