<?php

declare(strict_types=1);

namespace Duzzle\Validation\Strategy;

use Duzzle\DuzzleTarget;
use Duzzle\Validation\Exception\InputValidationFailedException;
use Duzzle\Validation\Exception\OutputValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class BlockingValidationStrategy implements ValidationStrategyInterface
{
    public function handleViolations(DuzzleTarget $target, mixed $value, ConstraintViolationListInterface $violations): void
    {
        if ($violations->count() > 0) {
            throw match ($target) {
                DuzzleTarget::INPUT => new InputValidationFailedException($violations),
                DuzzleTarget::OUTPUT => new OutputValidationFailedException($violations),
            };
        }
    }
}
