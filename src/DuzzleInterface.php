<?php

declare(strict_types=1);

namespace Duzzle;

use GuzzleHttp\Psr7\Response;

interface DuzzleInterface
{
    /**
     * @template TInputDto of object
     * @template TErrorDto of object
     * @template TOutputDto of object
     *
     * @param array{
     *      format?: string,
     *      input?: TInputDto,
     *      input_format?: string,
     *      output?: class-string<TOutputDto>,
     *      output_format?: string,
     *      error?: class-string<TErrorDto>,
     *      error_format?: string,
     *      headers?: array<string, string>,
     * } $options
     *
     * @return mixed|Response|TOutputDto|TErrorDto
     */
    public function request(string $method, string $url, array $options = []): mixed;
}
