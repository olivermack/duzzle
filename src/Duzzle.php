<?php

declare(strict_types=1);

namespace Duzzle;

use GuzzleHttp\ClientInterface;

final class Duzzle implements DuzzleInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly array $defaultOptions = []
    ) {
    }

    public function request(string $method, string $url, array $options = []): mixed
    {
        return $this->httpClient->request($method, $url, array_merge($this->defaultOptions, $options));
    }
}
