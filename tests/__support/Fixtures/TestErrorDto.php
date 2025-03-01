<?php

declare(strict_types=1);

namespace Duzzle\Tests\Fixtures;

class TestErrorDto
{
    //    public string $message;
    //    public int $code = 0;

    public function __construct(public string $message, public int $code)
    {
    }
}
