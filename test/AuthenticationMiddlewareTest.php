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
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface as ExpressiveUserInterface;

class AuthenticationMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    /** @psalm-var ObjectProphecy<AuthenticationInterface> */
    private $authentication;

    /** @psalm-var ObjectProphecy<ServerRequestInterface> */
    private $request;

    /** @psalm-var ObjectProphecy<UserInterface> */
    private $authenticatedUser;

    /** @psalm-var ObjectProphecy<RequestHandlerInterface> */
    private $handler;

    protected function setUp(): void
    {
        $this->authentication    = $this->prophesize(AuthenticationInterface::class);
        $this->request           = $this->prophesize(ServerRequestInterface::class);
        $this->authenticatedUser = $this->prophesize(UserInterface::class);
        $this->handler           = $this->prophesize(RequestHandlerInterface::class);
    }

    public function testConstructor(): void
    {
        $middleware = new AuthenticationMiddleware($this->authentication->reveal());
        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcessWithNoAuthenticatedUser(): void
    {
        $response = $this->prophesize(ResponseInterface::class);

        $this->authentication->authenticate($this->request->reveal())
                             ->willReturn(null);
        $this->authentication->unauthorizedResponse($this->request->reveal())
                             ->willReturn($response->reveal());

        $middleware = new AuthenticationMiddleware($this->authentication->reveal());
        $result     = $middleware->process($this->request->reveal(), $this->handler->reveal());

        $this->assertEquals($response->reveal(), $result);
        $this->authentication->unauthorizedResponse($this->request->reveal())->shouldBeCalled();
    }

    public function testProcessWithAuthenticatedUser(): void
    {
        $response = $this->prophesize(ResponseInterface::class);

        $this->request
            ->withAttribute(UserInterface::class, $this->authenticatedUser->reveal())
            ->willReturn($this->request->reveal());
        $this->request
            ->withAttribute(ExpressiveUserInterface::class, $this->authenticatedUser->reveal())
            ->willReturn($this->request->reveal());
        $this->authentication
            ->authenticate($this->request->reveal())
            ->willReturn($this->authenticatedUser->reveal());
        $this->handler
            ->handle($this->request->reveal())
            ->willReturn($response->reveal());

        $middleware = new AuthenticationMiddleware($this->authentication->reveal());
        $result     = $middleware->process($this->request->reveal(), $this->handler->reveal());

        $this->assertEquals($response->reveal(), $result);
        $this->handler->handle($this->request->reveal())->shouldBeCalled();
    }
}
