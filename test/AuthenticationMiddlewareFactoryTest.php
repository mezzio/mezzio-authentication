<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\AuthenticationMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AuthenticationMiddlewareFactoryTest extends TestCase
{
    protected $authentication;
    protected $request;

    public function setUp()
    {
        $this->authentication = $this->prophesize(AuthenticationInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new AuthenticationMiddlewareFactory();
    }

    /**
     * @expectedException Mezzio\Authentication\Exception\InvalidConfigException
     */
    public function testInvokeWithNoAuthenticationService()
    {
        $middleware = ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithAuthenticationService()
    {
        $this->container->has(AuthenticationInterface::class)
                        ->willReturn(true);
        $this->container->get(AuthenticationInterface::class)
                        ->willReturn($this->authentication->reveal());

        $middleware = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware);
    }
}
