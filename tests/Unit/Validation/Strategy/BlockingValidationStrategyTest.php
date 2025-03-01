<?php

declare(strict_types=1);

use Duzzle\DuzzleTarget;
use Duzzle\Validation\Exception\InputValidationFailedException;
use Duzzle\Validation\Exception\OutputValidationFailedException;
use Duzzle\Validation\Strategy\BlockingValidationStrategy;
use Symfony\Component\Validator\ConstraintViolationListInterface;

describe('BlockingValidationStrategy', function () {
    it('does not throw any exception if violation list is empty', function () {
        $sut = new BlockingValidationStrategy();
        $sut->handleViolations(DuzzleTarget::INPUT, null, Mockery::mock(ConstraintViolationListInterface::class, ['count' => 0]));
    })->throwsNoExceptions();

    it('throws InputValidationFailedException on INPUT target', function () {
        $sut = new BlockingValidationStrategy();
        $sut->handleViolations(DuzzleTarget::INPUT, null, Mockery::mock(ConstraintViolationListInterface::class, ['count' => 1]));
    })->throws(InputValidationFailedException::class);

    it('throws OutputValidationFailedException on OUTPUT target', function () {
        $sut = new BlockingValidationStrategy();
        $sut->handleViolations(DuzzleTarget::OUTPUT, null, Mockery::mock(ConstraintViolationListInterface::class, ['count' => 1]));
    })->throws(OutputValidationFailedException::class);
});
