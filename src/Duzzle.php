<?php

declare(strict_types=1);

namespace Duzzle;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

final readonly class Duzzle implements DuzzleInterface
{
    /**
     * @param array<string, mixed> $defaultOptions
     */
    public function __construct(
        private ClientInterface $httpClient,
        private array $defaultOptions = []
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, $url, array_merge($this->defaultOptions, $options));
    }
}
