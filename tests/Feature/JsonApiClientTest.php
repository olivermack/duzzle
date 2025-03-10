<?php

declare(strict_types=1);

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\DuzzleResponseInterface;
use Duzzle\Serialization\ContextBuilderInterface;
use Duzzle\Tests\Fixtures\PropertyWithResponseDto;
use Duzzle\Tests\Fixtures\TestErrorDto;
use Duzzle\Tests\Fixtures\TestPersonDto;
use Duzzle\Tests\Fixtures\TestPersonDtoWithPropertyPromotion;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use WireMock\Client\WireMock;

describe('JsonApi Client', function () {
    beforeEach(function () {
        $wireMockHost = $_ENV['WIREMOCK_HOST'] ?? 'http://wiremock:8080/';
        $parsedWireMockHost = parse_url($wireMockHost);
        $this->wireMock = WireMock::create($parsedWireMockHost['host'], $parsedWireMockHost['port']);
        $this->contextBuilderMock = Mockery::mock(ContextBuilderInterface::class, [
            'supportsNormalizationOf' => false,
            'supportsDenormalizationOf' => false,
        ]);
        $this->duzzle = DuzzleBuilder::create([
            'timeout' => 1.0,
            'base_uri' => $wireMockHost,
        ])
            ->withDefaultSerializer()
            ->withSerializationContextBuilder($this->contextBuilderMock)
            ->withDefaultValidator()
            ->build();
    });

    it('yields array for JSON response if no other options are set', function () {
        $res = $this->duzzle->request('GET', '/json-api/simple-get');
        expect($res->getDuzzleResult())
            ->toBeArray()
            ->toMatchArray(['foo' => 'bar']);
    });

    it('does not fail if invalid INPUT_FORMAT option given', function () {
        $res = $this->duzzle->request('GET', '/json-api/simple-get', [
            DuzzleOptionsKeys::INPUT_FORMAT => 1,
        ]);
        expect($res->getDuzzleResult())
            ->toBeArray()
            ->toMatchArray(['foo' => 'bar']);
    });

    it('does not fail if invalid OUTPUT_FORMAT option given', function () {
        $res = $this->duzzle->request('GET', '/json-api/simple-get', [
            DuzzleOptionsKeys::OUTPUT_FORMAT => 1,
        ]);
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

    it('serializes input DTO and output DTO', function () {
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

    it('fails when output DTO does not match type if context requests to enforce type', function () {
        $input = new TestPersonDtoWithPropertyPromotion('John', 'Doe', 123);
        $output = $this->duzzle->request('POST', '/json-api/post-person', [
            DuzzleOptionsKeys::INPUT => $input,
            DuzzleOptionsKeys::OUTPUT => TestPersonDto::class,
            DuzzleOptionsKeys::DENORMALIZATION_CONTEXT => [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => false,
            ],
        ]);
    })->throws(NotNormalizableValueException::class, 'The type of the "age" attribute for class "Duzzle\Tests\Fixtures\TestPersonDto" must be one of "int", "null" ("string" given)');

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
                ->and($error->getResponse()->getDuzzleResult()->code)->toBe(299)
                ->and($error->getResponse()->getDuzzleResult()->errors)->toContainOnlyInstancesOf(TestErrorDto::class)
                ->and($error->getResponse()->getDuzzleResult()->errors[0]->message)->toBe('Nested Error 1')
                ->and($error->getResponse()->getDuzzleResult()->errors[0]->code)->toBe(1)
                ->and($error->getResponse()->getDuzzleResult()->errors[1]->message)->toBe('Nested Error 2')
                ->and($error->getResponse()->getDuzzleResult()->errors[1]->code)->toBe(2)
            ;
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

    it('handles custom serializer context', function () {
        $class = new class {
            #[Groups(['group1', 'group2'])]
            public string $prop1 = '';
            #[Groups(['group2'])]
            public ?string $prop2 = null;
        };
        $instance = new $class();
        $instance->prop1 = 'input';

        $this->wireMock->stubFor(
            WireMock::get('/foo')
                ->withRequestBody(
                    WireMock::equalTo('{"prop1":"input"}')
                )
                ->willReturn(WireMock::aResponse()->withBody('{"prop2": "output"}'))
        );

        $res = $this->duzzle->request('GET', '/foo', [
            DuzzleOptionsKeys::OUTPUT => $class::class,
            DuzzleOptionsKeys::INPUT => $instance,
            DuzzleOptionsKeys::NORMALIZATION_CONTEXT => ['groups' => ['group1']],
            DuzzleOptionsKeys::DENORMALIZATION_CONTEXT => ['groups' => ['group2']],
        ]);
        expect($res->getDuzzleResult())
            ->toBeInstanceOf($class::class)
            ->and($res->getDuzzleResult()->prop2)->toBe('output');
    });

    it('handles uses custom + builder serializer context', function () {
        $class = new class {
            #[Groups(['group1', 'group2'])]
            public string $prop1 = '';
            #[Groups(['group2'])]
            public ?string $prop2 = null;
        };
        $instance = new $class();
        $instance->prop1 = 'input';

        $this->contextBuilderMock->expects('supportsNormalizationOf')
            ->once()
            ->withSomeOfArgs($instance)
            ->andReturn(true);
        $this->contextBuilderMock->expects('buildContextForNormalizationOf')
            ->once()
            ->withSomeOfArgs($instance)
            ->andReturn([
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            ]);

        $this->wireMock->stubFor(
            WireMock::get('/foo')
                ->withRequestBody(
                    WireMock::equalTo('{"prop1":"input"}')
                )
                ->willReturn(WireMock::aResponse()->withBody('{"prop2": "output"}'))
        );

        // while we use group1 + group2 here we use also SKIP_NULL_VALUES
        // so we can verify that the normalization context is indeed merged
        $res = $this->duzzle->request('GET', '/foo', [
            DuzzleOptionsKeys::OUTPUT => $class::class,
            DuzzleOptionsKeys::INPUT => $instance,
            DuzzleOptionsKeys::NORMALIZATION_CONTEXT => ['groups' => ['group1', 'group2']],
            DuzzleOptionsKeys::DENORMALIZATION_CONTEXT => ['groups' => ['group2']],
        ]);
        expect($res->getDuzzleResult())
            ->toBeInstanceOf($class::class)
            ->and($res->getDuzzleResult()->prop2)->toBe('output');
    });
});
