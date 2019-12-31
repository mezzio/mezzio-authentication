<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\DefaultUserFactory;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DefaultUserFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $factory = new DefaultUserFactory();
        $this->assertInstanceOf(DefaultUserFactory::class, $factory);
    }

    public function testInvokeWithIdentity()
    {
        $factory = new DefaultUserFactory();
        $userFactory = $factory($this->container->reveal());
        $defaultUser = $userFactory('foo');
        $this->assertInstanceOf(DefaultUser::class, $defaultUser);
        $this->assertEquals('foo', $defaultUser->getIdentity());
    }

    public function testInvokeWithIdentityAndRoles()
    {
        $factory = new DefaultUserFactory();
        $userFactory = $factory($this->container->reveal());
        $defaultUser = $userFactory('foo', ['admin', 'user']);
        $this->assertInstanceOf(DefaultUser::class, $defaultUser);
        $this->assertEquals('foo', $defaultUser->getIdentity());
        $this->assertEquals(['admin', 'user'], $defaultUser->getRoles());
    }

    public function testInvokeWithIdentityAndRolesAndDetails()
    {
        $factory = new DefaultUserFactory();
        $userFactory = $factory($this->container->reveal());
        $defaultUser = $userFactory('foo', ['admin', 'user'], ['email' => 'foo@test.com']);
        $this->assertInstanceOf(DefaultUser::class, $defaultUser);
        $this->assertEquals('foo', $defaultUser->getIdentity());
        $this->assertEquals(['admin', 'user'], $defaultUser->getRoles());
        $this->assertEquals(['email' => 'foo@test.com'], $defaultUser->getDetails());
        $this->assertEquals('foo@test.com', $defaultUser->getDetail('email'));
    }
}
