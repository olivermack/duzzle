<?php

declare(strict_types=1);

use Duzzle\DuzzleTarget;
use Duzzle\Validation\Strategy\InformativeValidationStrategy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

describe('InformativeValidationStrategy', function () {
    beforeEach(function () {
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->sut = new InformativeValidationStrategy($this->loggerMock);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('does nothing when list is empty', function () {
        $this->sut->handleViolations(DuzzleTarget::INPUT, null, Mockery::mock(ConstraintViolationListInterface::class, ['count' => 0]));
    })->throwsNoExceptions();

    it('invokes logger with details', function () {
        $violation = Mockery::mock(ConstraintViolation::class, [
            'getPropertyPath' => 'propertyPath',
            'getMessage' => 'message',
            'getCode' => 'code',
            'getInvalidValue' => 'invalidValue',
        ]);
        $this->loggerMock->shouldReceive('log')->once()->with(
            'warning',
            'Validation of input `null` failed with 1 violation(s)',
            [
                'violations' => [
                    [
                        'message' => $violation->getMessage(),
                        'code' => $violation->getCode(),
                        'property' => $violation->getPropertyPath(),
                        'value' => $violation->getInvalidValue(),
                    ],
                ],
            ],
        );
        $this->sut->handleViolations(DuzzleTarget::INPUT, null, Mockery::mock(ConstraintViolationListInterface::class, [
            'count' => 1,
            'getIterator' => new ArrayObject([
                $violation,
            ]),
        ]));
    });
});
