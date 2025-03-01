<?php

declare(strict_types=1);

namespace Duzzle\Exception;

use Psr\Http\Message\ResponseInterface;

class ResponseProcessingFailedException extends RuntimeException
{
    public function __construct(public readonly string $method, public readonly string $url, public readonly array $options, public readonly ResponseInterface $response)
    {
        parent::__construct(sprintf('Failed to process response for request to "%s"', $url));
    }
}
