<?php

declare(strict_types=1);

namespace Duzzle\Tests\Fixtures;

readonly class TestPersonDtoWithPropertyPromotion
{
    public function __construct(public string $firstName, public string $lastName, public int $age)
    {
    }
}
