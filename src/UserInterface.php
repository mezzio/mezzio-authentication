<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */
namespace Mezzio\Authentication;

interface UserInterface
{
    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     * Get the user role
     *
     * @return string
     */
    public function getUserRole(): string;
}
