<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace MezzioTest\Authentication;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddlewareTest extends TestCase
{
    protected $authentication;
    protected $request;

    public function setUp()
    {
        $this->authentication = $this->prophesize(AuthenticationInterface::class);
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->authenticatedUser = $this->prophesize(UserInterface::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
    }

    public function testConstructor()
    {
        $middleware = new AuthenticationMiddleware($this->authentication->reveal());
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware);
        $this->assertInstanceOf(ServerMiddlewareInterface::class, $middleware);
    }

    public function testProcessWithNoAuthenticatedUser()
    {
        $response = $this->prophesize(ResponseInterface::class);

        $this->authentication->authenticate($this->request->reveal())
                             ->willReturn(null);
        $this->authentication->unauthorizedResponse($this->request->reveal())
                             ->willReturn($response->reveal());

        $middleware = new AuthenticationMiddleware($this->authentication->reveal());
        $result = $middleware->process($this->request->reveal(), $this->delegate->reveal());

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals($response->reveal(), $result);
        $this->authentication->unauthorizedResponse($this->request->reveal())->shouldBeCalled();
    }

    public function testProcessWithAuthenticatedUser()
    {
        $response = $this->prophesize(ResponseInterface::class);

        $this->request->withAttribute(UserInterface::class, $this->authenticatedUser->reveal())
                      ->willReturn($this->request->reveal());
        $this->request->withAttribute(\Zend\Expressive\Authentication\UserInterface::class, $this->authenticatedUser->reveal())
                      ->willReturn($this->request->reveal());
        $this->authentication->authenticate($this->request->reveal())
                             ->willReturn($this->authenticatedUser->reveal());
        $this->delegate->process($this->request->reveal())
                       ->willReturn($response->reveal());

        $middleware = new AuthenticationMiddleware($this->authentication->reveal());
        $result = $middleware->process($this->request->reveal(), $this->delegate->reveal());

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals($response->reveal(), $result);
        $this->delegate->process($this->request->reveal())->shouldBeCalled();
    }
}
