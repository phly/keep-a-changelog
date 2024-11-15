<?php

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\TestAsset;

use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;
use Phly\KeepAChangelog\Provider\ProviderInterface;

abstract class AbstractMilestoneAwareProvider implements MilestoneAwareProviderInterface, ProviderInterface
{
}
