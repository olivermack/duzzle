<?php

declare(strict_types=1);

namespace Duzzle\Validation;

use Duzzle\Exception\MissingPackageException;
use PHPUnit\TextUI\XmlConfiguration\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class DefaultValidatorFactory
{
    public static function create(): ValidatorInterface
    {
        // @codeCoverageIgnoreStart
        if (!class_exists(Validator::class)) {
            throw new MissingPackageException('Please install "symfony/validator" package to use the default validator');
        }
        // @codeCoverageIgnoreEnd

        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
