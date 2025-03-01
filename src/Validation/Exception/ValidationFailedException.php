<?php

declare(strict_types=1);

namespace Duzzle\Validation\Exception;

use Duzzle\Exception\RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationFailedException extends RuntimeException
{
    public function __construct(public readonly ConstraintViolationListInterface $violations, string $message = 'Validation failed')
    {
        parent::__construct(sprintf('%s: %s', $message, $this->violations));
    }
}
