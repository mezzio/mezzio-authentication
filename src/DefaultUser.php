<?php

declare(strict_types=1);

namespace Mezzio\Authentication;

/**
 * Default implementation of UserInterface.
 *
 * This implementation is modeled as immutable, to prevent propagation of
 * user state changes.
 *
 * We recommend that any details injected are serializable.
 */
final class DefaultUser implements UserInterface
{
    /**
     * @psalm-param array<int|string, string> $roles
     * @psalm-param array<string, mixed> $details
     */
    public function __construct(
        private readonly string $identity,
        private readonly array $roles = [],
        private array $details = []
    ) {
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @psalm-return array<int|string, string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @psalm-return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param null|mixed $default Default value to return if no detail matching
     *     $name is discovered.
     * @return mixed
     */
    public function getDetail(string $name, $default = null)
    {
        return $this->details[$name] ?? $default;
    }
}
