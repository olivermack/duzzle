<?php

declare(strict_types=1);

namespace Duzzle\Tests\Fixtures;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TestNormalizer implements NormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return sprintf('%s normalized as %s', $data, $format);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return 'TestNormalizerInput' === $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['string' => true, '*' => false];
    }
}
