<?php

declare(strict_types=1);

namespace Mezzio\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface as ExpressiveUserInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /** @var AuthenticationInterface */
    protected $auth;

    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->authenticate($request);
        if (null !== $user) {
            return $handler->handle(
                $request
                    ->withAttribute(UserInterface::class, $user)
                    ->withAttribute(ExpressiveUserInterface::class, $user)
            );
        }
        return $this->auth->unauthorizedResponse($request);
    }
}
