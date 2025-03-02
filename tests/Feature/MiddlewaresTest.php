<?php

declare(strict_types=1);

namespace Feature;

use Duzzle\DuzzleOptionsKeys;
use Duzzle\DuzzleResponseInterface;
use Duzzle\Serialization\DefaultSerializerFactory;
use Duzzle\Serialization\SerializationHandler;
use Duzzle\Serialization\SerializationMiddleware;
use Duzzle\Tests\Fixtures\Todo;
use Duzzle\Validation\DefaultStrategyCollectionFactory;
use Duzzle\Validation\DefaultValidatorFactory;
use Duzzle\Validation\ValidationHandler;
use Duzzle\Validation\ValidationMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

describe('Middlewares Test', function () {
    it('handles output serialization via middleware', function () {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(
            new SerializationMiddleware(
                new SerializationHandler(DefaultSerializerFactory::create())
            ),
            'duzzle_serialization'
        );
        $stack->push(
            new ValidationMiddleware(
                new ValidationHandler(
                    DefaultValidatorFactory::create(),
                    DefaultStrategyCollectionFactory::create()
                )
            ),
            'duzzle_serialization'
        );

        $guzzle = new Client([
            'base_uri' => 'https://jsonplaceholder.typicode.com/',
            'handler' => $stack,
        ]);

        $res = $guzzle->request('GET', '/todos/1', [
            DuzzleOptionsKeys::OUTPUT => Todo::class,
        ]);

        if (!$res instanceof DuzzleResponseInterface) {
            $this->fail('Response should be instance of DuzzleResponseInterface');
        }

        expect($res->getStatusCode())
            ->toBe(200)
            ->and($res->getDuzzleResult())
            ->toBeInstanceOf(Todo::class)
            ->and($res->getDuzzleResult()->id)->toBe(1)
            ->and($res->getDuzzleResult()->userId)->toBe(1)
            ->and($res->getDuzzleResult()->title)->toBe('delectus aut autem')
            ->and($res->getDuzzleResult()->completed)->toBeFalse()
        ;
    });
});
