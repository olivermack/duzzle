<?php

declare(strict_types=1);

namespace Duzzle;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class DuzzleResponse extends Response implements DuzzleResponseInterface
{
    private mixed $duzzleResult = null;

    public static function fromPsrResponse(ResponseInterface $response, mixed $duzzleResult): self
    {
        $instance = new self(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase(),
        );
        $instance->duzzleResult = $duzzleResult;

        return $instance;
    }

    public function getDuzzleResult(): mixed
    {
        return $this->duzzleResult;
    }
}
