<?php

declare(strict_types=1);

namespace Duzzle;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

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
    public function request(string $method, string $url, array $options = []): DuzzleResponseInterface
    {
        $response = $this->httpClient->request($method, $url, array_merge($this->defaultOptions, $options));

        if (!$response instanceof DuzzleResponseInterface) {
            $response = DuzzleResponse::fromPsrResponse($response, null);
        }

        return $response;
    }
}
