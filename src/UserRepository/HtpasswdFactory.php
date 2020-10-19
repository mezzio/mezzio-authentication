<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\UserRepository;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class HtpasswdFactory
{
    /**
     * @throws Exception\InvalidConfigException
     */
    public function __invoke(ContainerInterface $container) : Htpasswd
    {
        $config = $container->get('config');
        Assert::isMap($config);
        $authConfig = $config['authentication'] ?? [];

        $htpasswd = $authConfig['htpasswd'] ?? null;
        Assert::nullOrString($htpasswd);

        if (null === $htpasswd) {
            throw new Exception\InvalidConfigException(sprintf(
                'Config key authentication.htpasswd is not present; cannot create %s user repository adapter',
                Htpasswd::class
            ));
        }

        $user = $container->get(UserInterface::class);
        Assert::isCallable($user);

        return new Htpasswd($htpasswd, $user);
    }
}
