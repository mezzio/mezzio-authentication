<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var AuthentiationInterface
     */
    protected $auth;

    /**
     * Constructor
     *
     * @param AuthenticationInterface $authentication
     * @return void
     */
    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $this->auth->authenticate($request);
        if (null !== $user) {
            return $delegate->process($request->withAttribute(UserInterface::class, $user)->withAttribute(\Zend\Expressive\Authentication\UserInterface::class, $user));
        }
        return $this->auth->unauthorizedResponse($request);
    }
}
