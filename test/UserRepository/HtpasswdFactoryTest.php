<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\Htpasswd;
use Mezzio\Authentication\UserRepository\HtpasswdFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class HtpasswdFactoryTest extends TestCase
{
    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->user = $this->prophesize(UserInterface::class);
        $this->factory = new HtpasswdFactory();
    }

    public function testInvokeWithMissingConfig()
    {
        // We cannot throw a ContainerExceptionInterface directly; this
        // approach simply mimics `get()` throwing _any_ exception, which is
        // what will happen if `config` is not defined.
        $this->container->get('config')->willThrow(new InvalidConfigException());

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithEmptyConfig()
    {
        $this->container->get('config')->willReturn([]);
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );

        $this->expectException(InvalidConfigException::class);
        $htpasswd = ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithInvalidConfig()
    {
        $this->container->get('config')->willReturn([
            'authentication' => [
                'htpasswd' => 'foo'
            ]
        ]);
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );

        $this->expectException(InvalidConfigException::class);
        $htpasswd = ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithValidConfig()
    {
        $this->container->get('config')->willReturn([
            'authentication' => [
                'htpasswd' => __DIR__ . '/../TestAssets/htpasswd'
            ]
        ]);
        $this->container->get(UserInterface::class)->willReturn(
            function () {
                return $this->user->reveal();
            }
        );

        $htpasswd = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(Htpasswd::class, $htpasswd);
    }
}
