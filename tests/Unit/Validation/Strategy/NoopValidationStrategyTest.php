<?php

declare(strict_types=1);

use Duzzle\DuzzleTarget;
use Duzzle\Validation\Strategy\NoopValidationStrategy;
use Symfony\Component\Validator\ConstraintViolationListInterface;

describe('NoopValidationStrategy', function () {
    it('just does noop', function () {
        $sut = new NoopValidationStrategy();
        $sut->handleViolations(DuzzleTarget::INPUT, null, Mockery::mock(ConstraintViolationListInterface::class, ['count' => 1]));
    })->throwsNoExceptions();
});
