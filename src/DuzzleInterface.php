<?php

declare(strict_types=1);

namespace Duzzle;

use Duzzle\Exception\UnableToProvideExpectedResultException;

interface DuzzleInterface
{
    /**
     * @template TInput of object
     * @template TOutput of object
     * @template TError of object
     *
     * @param array{
     *      format?: string,
     *      input?: TInput,
     *      input_format?: string,
     *      output?: class-string<TOutput>,
     *      output_format?: string,
     *      error?: class-string<TError>,
     *      error_format?: string,
     *      headers?: array<string, string>,
     * } $options
     *
     * @return DuzzleResponseInterface<TInput, TOutput, TError>
     *
     * @throws UnableToProvideExpectedResultException
     */
    public function request(string $method, string $url, array $options = []): DuzzleResponseInterface;
}
