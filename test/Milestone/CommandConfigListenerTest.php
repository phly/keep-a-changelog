<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CommandConfigListener;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class CommandConfigListenerTest extends TestCase
{
    /**
     * @return mixed
     */
    private function reflectParam(object $object, string $param)
    {
        $r = new ReflectionProperty($object, $param);
        $r->setAccessible(true);
        return $r->getValue($object);
    }

    public function testEnablesPackageNameAndRemoteNameRequirements(): void
    {
        $listener = new CommandConfigListener();
        $this->assertTrue($this->reflectParam($listener, 'requiresPackageName'));
        $this->assertTrue($this->reflectParam($listener, 'requiresRemoteName'));
    }
}
