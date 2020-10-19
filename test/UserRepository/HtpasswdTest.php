<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\Exception\RuntimeException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\Htpasswd;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class HtpasswdTest extends TestCase
{
    const EXAMPLE_IDENTITY = 'test';

    /** @psalm-var ObjectProphecy<UserInterface> */
    private $user;

    protected function setUp(): void
    {
        $this->user = $this->prophesize(UserInterface::class);
        $this->user->getIdentity()->willReturn(self::EXAMPLE_IDENTITY);
    }

    public function testConstructorWithNoFile(): void
    {
        $this->expectException(InvalidConfigException::class);

        new Htpasswd(
            'foo',
            function () {
                return $this->user->reveal();
            }
        );
    }

    public function testConstructor(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            function () {
                return $this->user->reveal();
            }
        );
        $this->assertInstanceOf(UserRepositoryInterface::class, $htpasswd);
    }

    public function testAuthenticate(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            function () {
                return $this->user->reveal();
            }
        );

        $user = $htpasswd->authenticate(self::EXAMPLE_IDENTITY, 'password');
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals(self::EXAMPLE_IDENTITY, $user->getIdentity());
    }

    public function testAuthenticateInvalidUser(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            function () {
                return $this->user->reveal();
            }
        );
        $this->assertNull($htpasswd->authenticate(self::EXAMPLE_IDENTITY, 'foo'));
    }

    public function testAuthenticateWithoutPassword(): void
    {
        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd',
            function () {
                return $this->user->reveal();
            }
        );
        $this->assertNull($htpasswd->authenticate(self::EXAMPLE_IDENTITY, null));
    }

    public function testAuthenticateWithInsecureHash(): void
    {
        $this->expectException(RuntimeException::class);

        $htpasswd = new Htpasswd(
            __DIR__ . '/../TestAssets/htpasswd_insecure',
            function () {
                return $this->user->reveal();
            }
        );
        $htpasswd->authenticate(self::EXAMPLE_IDENTITY, 'password');
    }
}
