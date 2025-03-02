<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

use Duzzle\DuzzleResponse;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

readonly class SerializationMiddleware
{
    public function __construct(
        private SerializationHandler $serializationHandler
    ) {
    }

    public function __invoke(callable $next): callable
    {
        return function (RequestInterface $request, array $options) use ($next): PromiseInterface {
            $options = $this->serializationHandler->handleInputSerialization($options);

            if (isset($options['body'])) {
                $request = $request->withBody(Utils::streamFor($options['body']));
                unset($options['body']);
            }

            if (isset($options['headers'])) {
                foreach ($options['headers'] as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }
                unset($options['headers']);
            }

            return $next($request, $options)->then(
                function (Response $response) use ($options) {
                    $deserializedResponse = $this->serializationHandler->handleResponseDeserialization($response, $options);

                    return DuzzleResponse::fromPsrResponse($response, $deserializedResponse);
                },
            );
        };
    }
}
