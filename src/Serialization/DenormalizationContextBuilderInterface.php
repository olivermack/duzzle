<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

interface DenormalizationContextBuilderInterface
{
    public function supportsDenormalizationOf(string $type, array $options = []): bool;

    public function buildContextForDenormalizationOf(string $type, array $options = []): array;
}
