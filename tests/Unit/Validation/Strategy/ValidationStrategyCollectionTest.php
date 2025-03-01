<?php

declare(strict_types=1);

use Duzzle\Validation\Strategy\ValidationStrategyCollection;
use Duzzle\Validation\Strategy\ValidationStrategyInterface;

describe('ValidationStrategyCollection', function () {
    it('bails when initializing with invalid key', function () {
        new ValidationStrategyCollection([10 => Mockery::mock(ValidationStrategyInterface::class)]);
    })->throws(Duzzle\Exception\InvalidArgumentException::class);

    it('has functional accessors for the initial storage', function () {
        $strategy = Mockery::mock(ValidationStrategyInterface::class);
        $sut = new ValidationStrategyCollection([
            'foo' => $strategy,
        ]);
        expect($sut->values())
            ->toHaveCount(1)
            ->and($sut->keys())->toBe(['foo'])
            ->and($sut->has('foo'))->toBeTrue()
            ->and($sut->has('bar'))->toBeFalse()
            ->and($sut->get('foo'))->toBe($strategy)
        ;
    });

    it('can remove existing entry', function () {
        $strategy = Mockery::mock(ValidationStrategyInterface::class);
        $sut = new ValidationStrategyCollection([
            'foo' => $strategy,
        ]);
        $sut->remove('foo');
        expect($sut->values())
            ->toHaveCount(0)
            ->and($sut->keys())->toBe([])
            ->and($sut->has('foo'))->toBeFalse()
            ->and($sut->has('bar'))->toBeFalse()
            ->and($sut->get('foo'))->toBeNull()
        ;
    });

    it('can remove add entry', function () {
        $strategy = Mockery::mock(ValidationStrategyInterface::class);
        $sut = new ValidationStrategyCollection();
        $sut->add('bar', $strategy);
        expect($sut->values())
            ->toHaveCount(1)
            ->and($sut->keys())->toBe(['bar'])
            ->and($sut->has('bar'))->toBeTrue()
            ->and($sut->get('bar'))->toBe($strategy)
        ;
    });

    it('has iterator', function () {
        $strategy = Mockery::mock(ValidationStrategyInterface::class);
        $sut = new ValidationStrategyCollection([
            'foo' => $strategy,
        ]);
        expect($sut->getIterator())
            ->toHaveCount(1)
        ;
    });
});
