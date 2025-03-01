<?php

declare(strict_types=1);

namespace Duzzle\Validation\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class InputValidationFailedException extends ValidationFailedException
{
    public function __construct(ConstraintViolationListInterface $violations)
    {
        parent::__construct($violations, 'Input Validation failed');
    }
}
