<?php

declare(strict_types=1);

namespace Duzzle\Validation;

use Duzzle\DuzzleResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

readonly class ValidationMiddleware
{
    public function __construct(
        private ValidationHandler $validationHandler
    ) {
    }

    public function __invoke(callable $next): callable
    {
        return function (RequestInterface $request, array $options) use ($next): PromiseInterface {
            $this->validationHandler->handleInputValidation($options);

            return $next($request, $options)->then(
                function (Response $response) use ($options) {
                    if ($response instanceof DuzzleResponseInterface) {
                        $this->validationHandler->handleOutputValidation($response->getDuzzleResult(), $options);
                    }

                    return $response;
                }
            );
        };
    }
}
