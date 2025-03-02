<?php

declare(strict_types=1);

namespace Duzzle\Validation;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class DefaultValidatorFactory
{
    public static function create(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
