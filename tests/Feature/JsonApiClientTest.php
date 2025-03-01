<?php

declare(strict_types=1);

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\Exception\RequestException;
use Duzzle\Tests\Fixtures\PropertyWithResponseDto;
use Duzzle\Tests\Fixtures\TestErrorDto;
use Duzzle\Tests\Fixtures\TestPersonDto;
use Duzzle\Tests\Fixtures\TestPersonDtoWithPropertyPromotion;
use GuzzleHttp\Psr7\Response;

describe('JsonApi Client', function () {
    beforeEach(function () {
        $this->httpClient = new GuzzleHttp\Client([
            'timeout' => 1.0,
            'base_uri' => 'http://wiremock:8080/',
        ]);

        $this->duzzle = DuzzleBuilder::create()
            ->withGuzzleClient($this->httpClient)
            ->withDefaultSerializer()
            ->withDefaultValidator()
            ->build();
    });

    it('yields array for JSON response if no other options are set', function () {
        $result = $this->duzzle->request('GET', '/json-api/simple-get');
        expect($result)
            ->toBeArray()
            ->toMatchArray(['foo' => 'bar']);
    });

    it('deserializes to DTO with property promotion', function () {
        $dto = $this->duzzle->request('GET', '/json-api/get-person', [
            DuzzleOptionsKeys::OUTPUT => TestPersonDtoWithPropertyPromotion::class,
        ]);
        expect($dto)
            ->toBeInstanceOf(TestPersonDtoWithPropertyPromotion::class)
            ->and($dto->firstName)->toBe('John')
            ->and($dto->lastName)->toBe('Doe')
            ->and($dto->age)->toBe(123);
    });

    it('serializes input DTO', function () {
        $input = new TestPersonDtoWithPropertyPromotion('John', 'Doe', 123);
        $output = $this->duzzle->request('POST', '/json-api/post-person', [
            DuzzleOptionsKeys::INPUT => $input,
            DuzzleOptionsKeys::OUTPUT => TestPersonDto::class,
        ]);
        expect($output)
            ->toBeInstanceOf(TestPersonDto::class)
            ->and($output->firstName)->toBe('John')
            ->and($output->lastName)->toBe('Doe')
            ->and($output->age)->toBe(123);
    });

    it('deserializes error to array when no ERROR type option set', function () {
        try {
            $this->duzzle->request('GET', '/json-api/simple-error');
        } catch (RequestException $error) {
            expect($error->error)
                ->toBeArray()
                ->toMatchArray(['message' => 'Something failed', 'code' => 299]);
        }
    });

    it('deserializes error DTO with public properties', function () {
        try {
            $this->duzzle->request('GET', '/json-api/simple-error', [
                DuzzleOptionsKeys::ERROR => TestErrorDto::class,
            ]);
        } catch (RequestException $error) {
            expect($error->error)
                ->toBeInstanceOf(TestErrorDto::class)
                ->and($error->error->message)->toBe('Something failed')
                ->and($error->error->code)->toBe(299);
        }
    });

    it('populates response if CarriesResponseInterface is implemented', function () {
        $res = $this->duzzle->request('GET', '/json-api/simple-get', [
            DuzzleOptionsKeys::OUTPUT => PropertyWithResponseDto::class,
        ]);
        expect($res)
            ->toBeInstanceOf(PropertyWithResponseDto::class)
            ->and($res->getResponse())->toBeInstanceOf(Response::class);
    });
});
