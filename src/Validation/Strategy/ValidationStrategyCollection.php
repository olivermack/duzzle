<?php

declare(strict_types=1);

namespace Duzzle\Validation\Strategy;

use Duzzle\Exception\InvalidArgumentException;

final class ValidationStrategyCollection implements \IteratorAggregate
{
    public function __construct(private array $storage = [])
    {
        foreach ($this->storage as $k => $v) {
            if (!is_string($k) || empty($k) || !$v instanceof ValidationStrategyInterface) {
                throw new InvalidArgumentException('Each entry in the initial storage needs to have a string-key and a valid value');
            }
        }
    }

    public function add(string $key, ValidationStrategyInterface $strategy): self
    {
        $this->storage[$key] = $strategy;

        return $this;
    }

    public function get(string $key): ?ValidationStrategyInterface
    {
        return $this->storage[$key] ?? null;
    }

    public function remove(string $key): void
    {
        unset($this->storage[$key]);
    }

    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->storage);
    }

    /**
     * @return ValidationStrategyInterface[]
     */
    public function values(): array
    {
        return array_values($this->storage);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->storage);
    }
}
