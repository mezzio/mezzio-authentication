<?php

declare(strict_types=1);

namespace Mezzio\Authentication;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * Produces a callable factory capable of itself producing a UserInterface
 * instance; this approach is used to allow substituting alternative user
 * implementations without requiring extensions to existing repositories.
 */
class DefaultUserFactory
{
    public function __invoke(ContainerInterface $container): callable
    {
        return static function (string $identity, array $roles = [], array $details = []): UserInterface {
            Assert::allString($roles);
            Assert::isMap($details);

            return new DefaultUser($identity, $roles, $details);
        };
    }
}
