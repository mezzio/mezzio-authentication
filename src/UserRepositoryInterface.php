<?php

declare(strict_types=1);

namespace Mezzio\Authentication;

interface UserRepositoryInterface
{
    /**
     * Authenticate the identity (id, username, email ...) using a password
     * or using only a credential string (e.g. token based credential)
     * It returns the authenticated user or null.
     *
     * @param string $credential can be also a token
     */
    public function authenticate(string $credential, ?string $password = null): ?UserInterface;
}
