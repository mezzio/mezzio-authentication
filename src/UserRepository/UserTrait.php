<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\UserInterface;

trait UserTrait
{
    /**
     * Generate a user from username and list of roles
     */
    protected function generateUser(string $username, ?array $roles = null) : UserInterface
    {
        return new class($username, $roles) implements UserInterface {
            private $username;
            private $roles;

            public function __construct(string $username, $roles)
            {
                $this->username = $username;
                $this->roles = $roles ?: [];
            }

            public function getUsername() : string
            {
                return $this->username;
            }

            public function getUserRoles() : array
            {
                return $this->roles;
            }
        };
    }
}
