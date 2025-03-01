<?php

declare(strict_types=1);

namespace Duzzle\Tests\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ValidatedPersonDtoWithPropertyPromotion
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 32)]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 32)]
        public string $lastName,
        #[Assert\NotBlank]
        #[Assert\Range(min: 10, max: 130)]
        public int $age
    ) {
    }
}
