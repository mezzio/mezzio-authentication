<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\Exception\RuntimeException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\Htpasswd;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Htpasswd::class)]
final class HtpasswdTest extends TestCase
{
    private const EXAMPLE_IDENTITY = 'test';

    /** @var UserInterface&MockObject */
    private UserInterface $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(UserInterface::class);

        $this->user
            ->expects(self::any())
            ->method('getIdentity')
            ->willReturn(self::EXAMPLE_IDENTITY);
    }

    public function testConstructorWithNoFile(): void
    {
        $this->expectException(InvalidConfigException::class);

        new Htpasswd(
            'foo',
            fn (): UserInterface => $this->user
        );
    }

    public function testConstructor(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            fn (): UserInterface => $this->user
        );

        self::assertInstanceOf(UserRepositoryInterface::class, $htpasswd);
    }

    public function testAuthenticate(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            fn (): UserInterface => $this->user
        );

        $user = $htpasswd->authenticate(self::EXAMPLE_IDENTITY, 'password');

        self::assertSame($this->user, $user);
        self::assertSame(self::EXAMPLE_IDENTITY, $user->getIdentity());
    }

    public function testAuthenticateInvalidUser(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            fn (): UserInterface => $this->user
        );

        self::assertNull($htpasswd->authenticate(self::EXAMPLE_IDENTITY, 'foo'));
    }

    public function testAuthenticateWithoutPassword(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            fn (): UserInterface => $this->user
        );

        self::assertNull($htpasswd->authenticate(self::EXAMPLE_IDENTITY, null));
    }

    public function testAuthenticateWithInsecureHash(): void
    {
        $this->expectException(RuntimeException::class);

        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd_insecure',
            fn (): UserInterface => $this->user
        );
        $htpasswd->authenticate(self::EXAMPLE_IDENTITY, 'password');
    }
}
