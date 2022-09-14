<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository\HtpasswdFactoryTest;

use ArrayAccess;
use OutOfBoundsException;
use ReturnTypeWillChange;

use function array_key_exists;
use function assert;
use function is_string;

/**
 * @see ReturnTypeWillChange
 *
 * @template-implements ArrayAccess<string,mixed>
 */
final class ConfigImplementingArrayAccess implements ArrayAccess
{
    /** @var array<array-key,mixed> */
    private array $data;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @psalm-param array-key $offset
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @psalm-param array-key $offset
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            throw new OutOfBoundsException();
        }

        return $this->data[$offset];
    }

    /**
     * @psalm-param array-key $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        assert(is_string($offset));
        $this->data[$offset] = $value;
    }

    /**
     * @psalm-param array-key $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
