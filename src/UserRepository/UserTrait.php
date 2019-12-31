<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\UserInterface;

trait UserTrait
{
    /**
     * Generate a user from identity and list of roles
     */
    protected function generateUser(string $identity, ?array $roles = null) : UserInterface
    {
        return new class($identity, $roles) implements UserInterface {
            private $identity;
            private $roles;

            public function __construct(string $identity, $roles)
            {
                $this->identity = $identity;
                $this->roles = $roles ?: [];
            }

            public function getIdentity() : string
            {
                return $this->identity;
            }

            public function getUserRoles() : array
            {
                return $this->roles;
            }
        };
    }
}
