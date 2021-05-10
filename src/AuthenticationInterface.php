<?php

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
    public function authenticate(ServerRequestInterface $request): ?UserInterface;

    /**
     * Generate the unauthorized response
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface;
}
