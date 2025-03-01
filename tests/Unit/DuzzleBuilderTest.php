<?php

declare(strict_types=1);

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleInterface;
use Duzzle\Serialization\ContextBuilderInterface;
use Duzzle\Serialization\SerializationDecorator;
use Duzzle\Validation\ValidationDecorator;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

describe('DuzzleBuilder', function () {
    it('builds without custom addons', function () {
        expect(DuzzleBuilder::create()->build())->toBeInstanceOf(DuzzleInterface::class);
    });

    it('builds with default decorators', function () {
        expect(
            DuzzleBuilder::create()
                ->withDefaultSerializer()
                ->withDefaultValidator()
                ->build()
        )->toBeInstanceOf(DuzzleInterface::class);
    });

    it('builds with custom http client', function () {
        $client = Mockery::mock(ClientInterface::class);
        expect(
            DuzzleBuilder::create()
                ->withGuzzleClient($client)
                ->build()
        )->toBeInstanceOf(DuzzleInterface::class);
    });

    it('builds with default serializer', function () {
        expect(
            DuzzleBuilder::create()
                ->withDefaultSerializer()
                ->build()
        )->toBeInstanceOf(SerializationDecorator::class);
    });

    it('builds with custom serializer', function () {
        expect(
            DuzzleBuilder::create()
                ->withSerializer(Mockery::mock(Serializer::class))
                ->build()
        )->toBeInstanceOf(SerializationDecorator::class);
    });

    it('builds with custom serializer + context builder', function () {
        expect(
            DuzzleBuilder::create()
                ->withSerializer(Mockery::mock(Serializer::class), Mockery::mock(ContextBuilderInterface::class))
                ->build()
        )->toBeInstanceOf(SerializationDecorator::class);
    });

    it('builds with default validator', function () {
        expect(
            DuzzleBuilder::create()
                ->withDefaultValidator()
                ->build()
        )->toBeInstanceOf(ValidationDecorator::class);
    });

    it('builds with custom validator', function () {
        expect(
            DuzzleBuilder::create()
                ->withValidator(Mockery::mock(ValidatorInterface::class))
                ->build()
        )->toBeInstanceOf(ValidationDecorator::class);
    });
});
