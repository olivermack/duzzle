<?php

declare(strict_types=1);

namespace Duzzle\Validation\Strategy;

use Duzzle\DuzzleTarget;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ValidationStrategyInterface
{
    public function handleViolations(DuzzleTarget $target, mixed $value, ConstraintViolationListInterface $violations): void;
}
