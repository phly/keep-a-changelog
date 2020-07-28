<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Milestone\CreateCommandConfigListener;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class CreateCommandConfigListenerTest extends TestCase
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
        $listener = new CreateCommandConfigListener();
        $this->assertTrue($this->reflectParam($listener, 'requiresPackageName'));
        $this->assertTrue($this->reflectParam($listener, 'requiresRemoteName'));
    }
}
