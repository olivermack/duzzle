<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

use Duzzle\DuzzleOptionsKeys;
use Duzzle\Exception\UnableToProvideExpectedResultException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;

readonly class SerializationHandler
{
    public function __construct(
        private Serializer $serializer,
        private ?ContextBuilderInterface $contextBuilder = null,
    ) {
    }

    public function handleInputSerialization(array $requestOptions): array
    {
        $inputType = $requestOptions[DuzzleOptionsKeys::INPUT] ?? null;
        $inputFormat = $requestOptions[DuzzleOptionsKeys::INPUT_FORMAT]
            ?? $requestOptions[DuzzleOptionsKeys::FORMAT]
            ?? null;

        if (null !== $inputFormat && !is_string($inputFormat)) {
            $inputFormat = null;
        }

        if (is_string($inputFormat) && !empty($inputType) && is_object($inputType)) {
            $context = true === $this->contextBuilder?->supportsNormalizationOf($inputType, $requestOptions)
                ? $this->contextBuilder->buildContextForNormalizationOf($inputType, $requestOptions)
                : [];
            $context = array_merge($context, $requestOptions[DuzzleOptionsKeys::NORMALIZATION_CONTEXT] ?? []);

            $serializedInput = $this->serializer->serialize($inputType, $inputFormat, $context);
            $requestOptions[RequestOptions::BODY] = $serializedInput;
        }

        return $this->enhanceContentTypeHeaderForInput($requestOptions, $inputFormat);
    }

    public function handleResponseDeserialization(Response $response, array $requestOptions): mixed
    {
        $response->getBody()->rewind();
        $contents = $response->getBody()->getContents();

        if ($response->getStatusCode() < 400) {
            $outputType = $requestOptions[DuzzleOptionsKeys::OUTPUT] ?? null;
            $outputFormat = $requestOptions[DuzzleOptionsKeys::OUTPUT_FORMAT]
                ?? $requestOptions[DuzzleOptionsKeys::FORMAT];
        } else {
            $outputType = $requestOptions[DuzzleOptionsKeys::ERROR] ?? null;
            $outputFormat = $requestOptions[DuzzleOptionsKeys::ERROR_FORMAT]
                ?? $requestOptions[DuzzleOptionsKeys::FORMAT];
        }

        if (!is_string($outputFormat)) {
            $outputFormat = $this->detectOutputFormatFromResponse($response);
        }

        $context = true === $this->contextBuilder?->supportsDenormalizationOf((string) $outputType, $requestOptions)
            ? $this->contextBuilder->buildContextForDenormalizationOf((string) $outputType, $requestOptions)
            : [];
        $context = array_merge($context, $requestOptions[DuzzleOptionsKeys::DENORMALIZATION_CONTEXT] ?? []);

        if (!empty($outputFormat) && !empty($outputType)) {
            $result = $this->serializer->deserialize($contents, $outputType, $outputFormat, $context);

            if ($result instanceof CarriesResponseInterface) {
                $result->setResponse($response);
            }
        } elseif (!empty($outputFormat)) {
            $result = $this->serializer->decode($contents, $outputFormat, $context);
        } else {
            $result = $contents;
        }

        if ($outputType && !is_a($result, $outputType, true)) {
            throw new UnableToProvideExpectedResultException(sprintf('It was not possible to yield a result of the desired type %s', $outputType));
        }

        return $result;
    }

    private function detectOutputFormatFromResponse(ResponseInterface $response): ?string
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
        if (null === $format) {
            return $requestOptions;
        }

        if (!isset($requestOptions[RequestOptions::HEADERS])) {
            $requestOptions[RequestOptions::HEADERS] = [];
        }

        if (!array_key_exists('Content-Type', $requestOptions[RequestOptions::HEADERS])) {
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
