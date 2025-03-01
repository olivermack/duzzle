<?php

declare(strict_types=1);

use Duzzle\DuzzleInterface;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\DuzzleTarget;
use Duzzle\Validation\Strategy\ValidationStrategyCollection;
use Duzzle\Validation\Strategy\ValidationStrategyInterface;
use Duzzle\Validation\ValidationDecorator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

describe('ValidationDecorator', function () {
    beforeEach(function () {
        $this->duzzleMock = Mockery::mock(DuzzleInterface::class);
        $this->validatorMock = Mockery::mock(ValidatorInterface::class);
        $this->strategyOne = Mockery::mock(ValidationStrategyInterface::class);
        $this->strategyTwo = Mockery::mock(ValidationStrategyInterface::class);
        $this->strategies = new ValidationStrategyCollection([
            'one' => $this->strategyOne,
            'two' => $this->strategyTwo,
        ]);
        $this->sut = new ValidationDecorator($this->duzzleMock, $this->validatorMock, $this->strategies);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('passes on empty violations', function () {
        $this->duzzleMock->shouldReceive('request')->andReturn('something');
        $this->validatorMock->shouldReceive('validate')->andReturn(Mockery::mock(ConstraintViolationListInterface::class));
        $res = $this->sut->request('POST', '/foo');
        expect($res)->toBe('something');
    });

    it('passes on non-empty violations when using default strategies', function () {
        $violations = ConstraintViolationList::createFromMessage('Some Message');
        $this->duzzleMock->shouldReceive('request')->andReturn('something');
        $this->validatorMock->shouldReceive('validate')->andReturn($violations);
        $res = $this->sut->request('POST', '/foo');
        expect($res)->toBe('something');
    });

    it('invokes requested strategy with value and violations', function () {
        $input = 'Some Input';
        $violations = ConstraintViolationList::createFromMessage('Some Message');
        $this->strategyTwo->shouldReceive('handleViolations')->once()->with(DuzzleTarget::INPUT, $input, $violations);
        $this->duzzleMock->shouldReceive('request')->andReturn('something');
        $this->validatorMock->shouldReceive('validate')->andReturn($violations);
        $res = $this->sut->request('POST', '/foo', [
            DuzzleOptionsKeys::INPUT => $input,
            ValidationDecorator::INPUT_VALIDATION => 'two',
        ]);
        expect($res)->toBe('something');
    });
});
