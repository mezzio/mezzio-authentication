<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\UserRepository\HtpasswdFactoryTest;

use ArrayAccess;
use OutOfBoundsException;

use function array_key_exists;
use function assert;
use function is_string;

/**
 * @template-implements ArrayAccess<string,mixed>
 */
final class ConfigImplementingArrayAccess implements ArrayAccess
{
    /** @var array<array-key,mixed> */
    private $data;

    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @psalm-param array-key $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @psalm-param array-key $offset
     * @return mixed
     */
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
    public function offsetSet($offset, $value)
    {
        assert(is_string($offset));
        $this->data[$offset] = $value;
    }

    /**
     * @psalm-param array-key $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
