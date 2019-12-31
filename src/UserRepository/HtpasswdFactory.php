<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\Exception;
use Psr\Container\ContainerInterface;

class HtpasswdFactory
{
    public function __invoke(ContainerInterface $container) : Htpasswd
    {
        $htpasswd = $container->get('config')['authentication']['htpasswd'] ?? null;
        if (null === $htpasswd) {
            throw new Exception\InvalidConfigException(
                'Htpasswd file name is not present in user_register config'
            );
        }
        return new Htpasswd($htpasswd);
    }
}
