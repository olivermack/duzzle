<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

interface NormalizationContextBuilderInterface
{
    public function supportsNormalizationOf(object $input): bool;

    public function buildContextForNormalizationOf(object $input): array;
}
