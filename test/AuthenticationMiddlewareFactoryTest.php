<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\AuthenticationMiddlewareFactory;
use Mezzio\Authentication\Exception\InvalidConfigException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class AuthenticationMiddlewareFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @psalm-var ObjectProphecy<ContainerInterface> */
    private $container;

    /** @psalm-var ObjectProphecy<AuthenticationInterface> */
    private $authentication;

    /** @var AuthenticationMiddlewareFactory */
    private $factory;

    protected function setUp(): void
    {
        $this->authentication = $this->prophesize(AuthenticationInterface::class);
        $this->container      = $this->prophesize(ContainerInterface::class);
        $this->factory        = new AuthenticationMiddlewareFactory();
    }

    public function testInvokeWithNoAuthenticationService(): void
    {
        $this->container->has(AuthenticationInterface::class)
                        ->willReturn(false);

        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testInvokeWithAuthenticationService(): void
    {
        $this->container->has(AuthenticationInterface::class)
                        ->willReturn(true);
        $this->container->get(AuthenticationInterface::class)
                        ->willReturn($this->authentication->reveal());

        $middleware = ($this->factory)($this->container->reveal());
        $this->assertEquals(new AuthenticationMiddleware($this->authentication->reveal()), $middleware);
    }
}
