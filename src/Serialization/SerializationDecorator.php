<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

use Duzzle\DuzzleInterface;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\Exception\RequestException;
use Duzzle\Exception\ResponseProcessingFailedException;
use Duzzle\Exception\RuntimeException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class SerializationDecorator implements DuzzleInterface
{
    public function __construct(
        private readonly DuzzleInterface $decorated,
        private readonly SerializerInterface $serializer,
        private readonly ?ContextBuilderInterface $contextBuilder = null,
        private readonly array $defaultOptions = []
    ) {
    }

    public function request(string $method, string $url, array $options = []): mixed
    {
        try {
            $options = $this->prepareInputFromOptions($options);
            $response = $this->decorated->request($method, $url, array_merge($this->defaultOptions, $options));

            if (!$response instanceof ResponseInterface) {
                throw new RuntimeException('The response needs to be an instance of Psr\Http\Message\ResponseInterface');
            }

            return $this->handleResponse($response, $options);
        } catch (GuzzleRequestException $e) {
            throw $this->mapRequestException($e, $options);
        }

        throw new ResponseProcessingFailedException($method, $url, $options, $response);
    }

    private function prepareInputFromOptions(array $requestOptions): array
    {
        $inputType = $requestOptions[DuzzleOptionsKeys::INPUT] ?? null;
        $inputFormat = $requestOptions[DuzzleOptionsKeys::INPUT_FORMAT]
            ?? $requestOptions[DuzzleOptionsKeys::FORMAT]
            ?? null;

        if ($inputFormat && $inputType) {
            $context = $this->contextBuilder?->supportsNormalizationOf($inputType)
                ? $this->contextBuilder->buildContextForNormalizationOf($inputType)
                : [];

            $serializedInput = $this->serializer->serialize($inputType, $inputFormat, $context);
            $requestOptions[RequestOptions::BODY] = $serializedInput;
        }

        return $this->enhanceContentTypeHeaderForInput($requestOptions, $inputFormat);
    }

    /**
     * @throws SerializerExceptionInterface
     */
    private function handleResponse(Response $response, array $requestOptions): mixed
    {
        $contents = $response->getBody()->getContents();
        $outputType = $requestOptions[DuzzleOptionsKeys::OUTPUT] ?? null;
        $outputFormat = $requestOptions[DuzzleOptionsKeys::OUTPUT_FORMAT]
            ?? $requestOptions[DuzzleOptionsKeys::FORMAT]
            ?? $this->detectOutputFormatFromResponse($response);
        $context = $this->contextBuilder?->supportsDenormalizationOf($outputType)
            ? $this->contextBuilder->buildContextForDenormalizationOf($outputType)
            : [];

        if ($outputFormat && $outputType) {
            $result = $this->serializer->deserialize($contents, $outputType, $outputFormat, $context);

            if ($result instanceof CarriesResponseInterface) {
                $result->setResponse($response);
            }

            return $result;
        } elseif ($outputFormat && $this->serializer instanceof DecoderInterface) {
            return $this->serializer->decode($contents, $outputFormat, $context);
        }

        return $contents;
    }

    private function mapRequestException(GuzzleRequestException $e, array $requestOptions): RequestException
    {
        $errorFormat = $requestOptions[DuzzleOptionsKeys::ERROR_FORMAT]
            ?? $requestOptions[DuzzleOptionsKeys::FORMAT]
            ?? $this->detectOutputFormatFromResponse($e->getResponse());
        $errorType = $requestOptions[DuzzleOptionsKeys::ERROR] ?? null;
        $e->getResponse()?->getBody()->rewind();
        $errorBody = $e->getResponse()?->getBody()->getContents();
        $context = $this->contextBuilder?->supportsDenormalizationOf($errorType)
            ? $this->contextBuilder->buildContextForDenormalizationOf($errorType)
            : [];

        if ($errorFormat && $errorType) {
            $errorBody = $this->serializer->deserialize(
                $errorBody,
                $errorType,
                $errorFormat,
                $context,
            );
        } elseif ($errorFormat && $this->serializer instanceof DecoderInterface) {
            $errorBody = $this->serializer->decode($errorBody, $errorFormat, $context);
        }

        return new RequestException($errorBody, $e->getCode(), $e);
    }

    private function detectOutputFormatFromResponse(Response $response): ?string
    {
        $contentType = $response->getHeaderLine('Content-type');

        if (str_contains(strtolower($contentType), 'json')) {
            return 'json';
        } elseif (str_contains(strtolower($contentType), 'xml')) {
            return 'xml';
        }

        return null;
    }

    private function enhanceContentTypeHeaderForInput(array $requestOptions, ?string $format = null): array
    {
        if (!$format) {
            return $requestOptions;
        }

        if (!isset($requestOptions[RequestOptions::HEADERS]) || !array_key_exists('Content-Type', $requestOptions[RequestOptions::HEADERS])) {
            $requestOptions[RequestOptions::HEADERS]['Content-Type'] = $this->getContentTypeHeaderForFormat($format);
        }

        return $requestOptions;
    }

    private function getContentTypeHeaderForFormat(string $format): ?string
    {
        return match ($format) {
            'json' => 'application/json',
            'xml' => 'application/xml',
            default => null,
        };
    }
}
