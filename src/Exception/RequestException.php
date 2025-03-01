<?php

declare(strict_types=1);

namespace Duzzle\Exception;

class RequestException extends \RuntimeException implements DuzzleExceptionInterface
{
    /**
     * @template TError of object|string
     *
     * @param TError $error
     */
    public function __construct(public readonly mixed $error, int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct('Request failed', $code, $previous);
    }
}
