<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationInterface
{
    /**
     * Authenticate the PSR-7 request and return a valid user
     * or null if not authenticated
     */
    public function authenticate(ServerRequestInterface $request) : ?UserInterface;

    /**
     * Generate the unauthorized response
     */
    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface;
}
