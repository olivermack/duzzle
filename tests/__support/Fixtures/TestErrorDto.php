<?php

declare(strict_types=1);

namespace Duzzle\Tests\Fixtures;

class TestErrorDto
{
    public function __construct(
        public string $message,
        public int $code,
        /** @var TestErrorDto[] $errors */
        public array $errors = []
    ) {
    }
}
