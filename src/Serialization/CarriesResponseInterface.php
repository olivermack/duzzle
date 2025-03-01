<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

use GuzzleHttp\Psr7\Response;

interface CarriesResponseInterface
{
    public function setResponse(?Response $response): void;

    public function getResponse(): ?Response;
}
