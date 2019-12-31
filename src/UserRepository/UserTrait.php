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
     * Generate a user from $username and $role
     *
     * @param string $username
     * @param string $role
     * @return UserInterface
     */
    protected function generateUser(string $username, string $role): UserInterface
    {
        return new class($username, $role) implements UserInterface {
            public function __construct($username, $role)
            {
                $this->username = $username;
                $this->role = $role;
            }

            public function getUsername(): string
            {
                return $this->username;
            }

            public function getUserRole(): string
            {
                return $this->role;
            }
        };
    }
}
