<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;

class DefaultUserTest extends TestCase
{
    public function testConstructor()
    {
        $user = new DefaultUser('foo');
        $this->assertInstanceOf(UserInterface::class, $user);
    }

    public function testGetIdentity()
    {
        $user = new DefaultUser('foo');
        $this->assertEquals('foo', $user->getIdentity());
    }

    public function testGetRoles()
    {
        $user = new DefaultUser('foo', ['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $user->getRoles());
    }

    public function testGetDetails()
    {
        $user = new DefaultUser('foo', ['foo', 'bar'], ['name' => 'Foo']);
        $this->assertEquals(['name' => 'Foo'], $user->getDetails());
    }

    public function testGetDetail()
    {
        $user = new DefaultUser('foo', ['foo', 'bar'], ['name' => 'Foo']);
        $this->assertEquals('Foo', $user->getDetail('name'));
        $this->assertEquals(false, $user->getDetail('email', false));
        $this->assertEquals(null, $user->getDetail('email'));
    }
}
