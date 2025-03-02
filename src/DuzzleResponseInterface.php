<?php

declare(strict_types=1);

namespace Duzzle;

/**
 * @psalm-template TInput of object
 * @psalm-template TOutput of object
 * @psalm-template TError of object
 */
interface DuzzleResponseInterface
{
    /**
     * @return mixed|TOutput
     */
    public function getDuzzleResult(): mixed;
}
