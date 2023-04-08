<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultUser::class)]
final class DefaultUserTest extends TestCase
{
    public function testConstructor(): void
    {
        $user = new DefaultUser('foo');

        self::assertInstanceOf(UserInterface::class, $user);
    }

    public function testGetIdentity(): void
    {
        $user = new DefaultUser('foo');

        self::assertSame('foo', $user->getIdentity());
    }

    public function testGetRoles(): void
    {
        $user = new DefaultUser('foo', ['foo', 'bar']);

        self::assertSame(['foo', 'bar'], $user->getRoles());
    }

    public function testGetDetails(): void
    {
        $user = new DefaultUser('foo', ['foo', 'bar'], ['name' => 'Foo']);

        self::assertSame(['name' => 'Foo'], $user->getDetails());
    }

    public function testGetDetail(): void
    {
        $user = new DefaultUser('foo', ['foo', 'bar'], ['name' => 'Foo']);

        self::assertSame('Foo', $user->getDetail('name'));
        self::assertFalse($user->getDetail('email', false));
        self::assertNull($user->getDetail('email'));
    }
}
