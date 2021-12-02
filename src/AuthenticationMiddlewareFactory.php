<?php

declare(strict_types=1);

namespace Mezzio\Authentication;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class AuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationMiddleware
    {
        $authentication = $container->has(AuthenticationInterface::class)
            ? $container->get(AuthenticationInterface::class)
            : null;

        Assert::nullOrIsInstanceOf($authentication, AuthenticationInterface::class);

        if (null === $authentication) {
            throw new Exception\InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new AuthenticationMiddleware($authentication);
    }
}
