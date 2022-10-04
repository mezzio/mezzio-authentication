<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\AuthenticationMiddlewareFactory;
use Mezzio\Authentication\Exception\InvalidConfigException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/** @covers \Mezzio\Authentication\AuthenticationMiddlewareFactory */
final class AuthenticationMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    /** @var AuthenticationInterface&MockObject */
    private AuthenticationInterface $authentication;

    private AuthenticationMiddlewareFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authentication = $this->createMock(AuthenticationInterface::class);
        $this->container      = $this->createMock(ContainerInterface::class);

        $this->factory = new AuthenticationMiddlewareFactory();
    }

    public function testInvokeWithNoAuthenticationService(): void
    {
        $this->container
            ->expects(self::once())
            ->method('has')
            ->with(AuthenticationInterface::class)
            ->willReturn(false);

        $this->container
            ->expects(self::never())
            ->method('get')
            ->with(AuthenticationInterface::class);

        $this->expectException(InvalidConfigException::class);

        ($this->factory)($this->container);
    }

    public function testInvokeWithAuthenticationService(): void
    {
        $this->container
            ->expects(self::once())
            ->method('has')
            ->with(AuthenticationInterface::class)
            ->willReturn(true);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(AuthenticationInterface::class)
            ->willReturn($this->authentication);

        $middleware = ($this->factory)($this->container);

        self::assertEquals(new AuthenticationMiddleware($this->authentication), $middleware);
    }
}
