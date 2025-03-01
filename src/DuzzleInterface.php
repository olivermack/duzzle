<?php

declare(strict_types=1);

namespace Duzzle;

interface DuzzleInterface
{
    /**
     * @template TInputDto of object
     * @template TErrorDto of object
     * @template TOutputDto of object
     *
     * @param array{
     *     input?: TInputDto,
     *     output?: class-string<TOutputDto>,
     *     error?: class-string<TErrorDto>,
     * } $options
     *
     * @return TOutputDto|TErrorDto
     */
    public function request(string $method, string $url, array $options = []): mixed;
}
