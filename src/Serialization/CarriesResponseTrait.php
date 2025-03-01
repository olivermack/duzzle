<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

use GuzzleHttp\Psr7\Response;

trait CarriesResponseTrait
{
    private ?Response $response = null;

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
