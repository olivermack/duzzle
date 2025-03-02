<?php

declare(strict_types=1);

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\Tests\Fixtures\ValidatedPersonDtoWithPropertyPromotion;
use Duzzle\Validation\Exception\InputValidationFailedException;
use Duzzle\Validation\Exception\OutputValidationFailedException;
use Duzzle\Validation\Strategy\DefaultStrategyKey;
use Duzzle\Validation\ValidationDecorator;

describe('Validated JsonApi Client', function () {
    beforeEach(function () {
        $this->httpClient = new GuzzleHttp\Client([
            'timeout' => 1.0,
            'base_uri' => $_ENV['WIREMOCK_HOST'] ?? 'http://wiremock:8080/',
        ]);

        $this->duzzle = DuzzleBuilder::create([
            ValidationDecorator::INPUT_VALIDATION => DefaultStrategyKey::BLOCKING->value,
            ValidationDecorator::OUTPUT_VALIDATION => DefaultStrategyKey::BLOCKING->value,
        ])
            ->withGuzzleClient($this->httpClient)
            ->withDefaultSerializer()
            ->withDefaultValidator()
            ->build();
    });

    it('deserializes to validated DTO with property promotion', function () {
        $dto = $this->duzzle->request('GET', '/json-api/get-person', [
            DuzzleOptionsKeys::OUTPUT => ValidatedPersonDtoWithPropertyPromotion::class,
        ]);
        expect($dto)
            ->toBeInstanceOf(ValidatedPersonDtoWithPropertyPromotion::class)
            ->and($dto->firstName)->toBe('John')
            ->and($dto->lastName)->toBe('Doe')
            ->and($dto->age)->toBe(123);
    });

    it('bails if output DTO is invalid', function () {
        $this->duzzle->request('GET', '/json-api/get-person-invalid', [
            DuzzleOptionsKeys::OUTPUT => ValidatedPersonDtoWithPropertyPromotion::class,
        ]);
    })->throws(OutputValidationFailedException::class);

    it('bails if input DTO is invalid', function () {
        $input = new ValidatedPersonDtoWithPropertyPromotion('', '', 0);
        $this->duzzle->request('POST', '/json-api/get-person-invalid', [
            DuzzleOptionsKeys::INPUT => $input,
            DuzzleOptionsKeys::OUTPUT => ValidatedPersonDtoWithPropertyPromotion::class,
        ]);
    })->throws(InputValidationFailedException::class);
});
