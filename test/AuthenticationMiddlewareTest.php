<?php

declare(strict_types=1);

namespace MezzioTest\Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** @covers \Mezzio\Authentication\AuthenticationMiddleware */
final class AuthenticationMiddlewareTest extends TestCase
{
    /** @var AuthenticationInterface&MockObject */
    private AuthenticationInterface $authentication;

    /** @var ServerRequestInterface&MockObject */
    private ServerRequestInterface $request;

    /** @var UserInterface&MockObject */
    private UserInterface $authenticatedUser;

    /** @var RequestHandlerInterface&MockObject */
    private RequestHandlerInterface $handler;

    private AuthenticationMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authentication    = $this->createMock(AuthenticationInterface::class);
        $this->request           = $this->createMock(ServerRequestInterface::class);
        $this->authenticatedUser = $this->createMock(UserInterface::class);
        $this->handler           = $this->createMock(RequestHandlerInterface::class);

        $this->middleware = new AuthenticationMiddleware($this->authentication);
    }

    public function testConstructor(): void
    {
        self::assertInstanceOf(MiddlewareInterface::class, $this->middleware);
    }

    public function testProcessWithNoAuthenticatedUser(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->authentication
            ->expects(self::once())
            ->method('authenticate')
            ->with($this->request)
            ->willReturn(null);

        $this->authentication
            ->expects(self::once())
            ->method('unauthorizedResponse')
            ->with($this->request)
            ->willReturn($response);

        self::assertSame($response, $this->middleware->process($this->request, $this->handler));
    }

    public function testProcessWithAuthenticatedUser(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->request
            ->expects(self::once())
            ->method('withAttribute')
            ->with(UserInterface::class, $this->authenticatedUser)
            ->willReturn($this->request);

        $this->authentication
            ->expects(self::once())
            ->method('authenticate')
            ->with($this->request)
            ->willReturn($this->authenticatedUser);

        $this->handler
            ->expects(self::once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($response);

        self::assertSame($response, $this->middleware->process($this->request, $this->handler));
    }
}
