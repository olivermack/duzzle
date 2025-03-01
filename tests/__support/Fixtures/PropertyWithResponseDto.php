<?php

declare(strict_types=1);

namespace Duzzle\Tests\Fixtures;

use Duzzle\Serialization\CarriesResponseInterface;
use Duzzle\Serialization\CarriesResponseTrait;

class PropertyWithResponseDto implements CarriesResponseInterface
{
    use CarriesResponseTrait;

    public function __construct(public ?string $foo)
    {
    }
}
