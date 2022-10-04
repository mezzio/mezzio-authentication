<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\DefaultUserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/** @covers \Mezzio\Authentication\DefaultUserFactory */
final class DefaultUserFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    private DefaultUserFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->factory = new DefaultUserFactory();
    }

    public function testInvokeWithIdentity(): void
    {
        $userFactory = $this->factory->__invoke($this->container);
        $defaultUser = $userFactory('foo');

        self::assertInstanceOf(DefaultUser::class, $defaultUser);
        self::assertSame('foo', $defaultUser->getIdentity());
    }

    public function testInvokeWithIdentityAndRoles(): void
    {
        $userFactory = $this->factory->__invoke($this->container);
        $defaultUser = $userFactory('foo', ['admin', 'user']);

        self::assertInstanceOf(DefaultUser::class, $defaultUser);
        self::assertSame('foo', $defaultUser->getIdentity());
        self::assertSame(['admin', 'user'], $defaultUser->getRoles());
    }

    public function testInvokeWithIdentityAndRolesAndDetails(): void
    {
        $userFactory = $this->factory->__invoke($this->container);
        $defaultUser = $userFactory('foo', ['admin', 'user'], ['email' => 'foo@test.com']);

        self::assertInstanceOf(DefaultUser::class, $defaultUser);
        self::assertSame('foo', $defaultUser->getIdentity());
        self::assertSame(['admin', 'user'], $defaultUser->getRoles());
        self::assertSame(['email' => 'foo@test.com'], $defaultUser->getDetails());
        self::assertSame('foo@test.com', $defaultUser->getDetail('email'));
    }
}
