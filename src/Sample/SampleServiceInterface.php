<?php

declare(strict_types=1);

namespace Duzzle\Sample;

interface SampleServiceInterface
{
    /**
     * @template TObject of object
     *
     * @param array{
     *     test?: class-string<TObject>
     * } $options
     *
     * @return TObject
     */
    public function doStuff(array $options = []): object;
}
