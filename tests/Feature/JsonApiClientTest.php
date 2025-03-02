<?php

declare(strict_types=1);

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\DuzzleResponseInterface;
use Duzzle\Tests\Fixtures\PropertyWithResponseDto;
use Duzzle\Tests\Fixtures\TestErrorDto;
use Duzzle\Tests\Fixtures\TestPersonDto;
use Duzzle\Tests\Fixtures\TestPersonDtoWithPropertyPromotion;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

describe('JsonApi Client', function () {
    beforeEach(function () {
        $this->duzzle = DuzzleBuilder::create([
            'timeout' => 1.0,
            'base_uri' => $_ENV['WIREMOCK_HOST'] ?? 'http://wiremock:8080/',
        ])
            ->withDefaultSerializer()
            ->withDefaultValidator()
            ->build();
    });

    it('yields array for JSON response if no other options are set', function () {
        $res = $this->duzzle->request('GET', '/json-api/simple-get');
        expect($res->getDuzzleResult())
            ->toBeArray()
            ->toMatchArray(['foo' => 'bar']);
    });

    it('deserializes to DTO with property promotion', function () {
        $res = $this->duzzle->request('GET', '/json-api/get-person', [
            DuzzleOptionsKeys::OUTPUT => TestPersonDtoWithPropertyPromotion::class,
        ]);
        expect($res->getDuzzleResult())
            ->toBeInstanceOf(TestPersonDtoWithPropertyPromotion::class)
            ->and($res->getDuzzleResult()->firstName)->toBe('John')
            ->and($res->getDuzzleResult()->lastName)->toBe('Doe')
            ->and($res->getDuzzleResult()->age)->toBe(123);
    });

    it('serializes input DTO', function () {
        $input = new TestPersonDtoWithPropertyPromotion('John', 'Doe', 123);
        $output = $this->duzzle->request('POST', '/json-api/post-person', [
            DuzzleOptionsKeys::INPUT => $input,
            DuzzleOptionsKeys::OUTPUT => TestPersonDto::class,
        ]);
        expect($output->getDuzzleResult())
            ->toBeInstanceOf(TestPersonDto::class)
            ->and($output->getDuzzleResult()->firstName)->toBe('John')
            ->and($output->getDuzzleResult()->lastName)->toBe('Doe')
            ->and($output->getDuzzleResult()->age)->toBe(123);
    });

    it('deserializes error to array when no ERROR type option set', function () {
        try {
            $this->duzzle->request('GET', '/json-api/simple-error');
        } catch (RequestException $error) {
            expect($error->getResponse())->toBeInstanceOf(DuzzleResponseInterface::class)
                ->and($error->getResponse()->getDuzzleResult())
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
            expect($error->getResponse())->toBeInstanceOf(DuzzleResponseInterface::class)
                ->and($error->getResponse()->getDuzzleResult())
                ->toBeInstanceOf(TestErrorDto::class)
                ->and($error->getResponse()->getDuzzleResult()->message)->toBe('Something failed')
                ->and($error->getResponse()->getDuzzleResult()->code)->toBe(299);
        }
    });

    it('populates response if CarriesResponseInterface is implemented', function () {
        $res = $this->duzzle->request('GET', '/json-api/simple-get', [
            DuzzleOptionsKeys::OUTPUT => PropertyWithResponseDto::class,
        ]);
        expect($res->getDuzzleResult())
            ->toBeInstanceOf(PropertyWithResponseDto::class)
            ->and($res->getDuzzleResult()->getResponse())->toBeInstanceOf(Response::class);
    });
});
