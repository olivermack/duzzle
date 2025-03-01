<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

interface DenormalizationContextBuilderInterface
{
    public function supportsDenormalizationOf(string $type): bool;

    public function buildContextForDenormalizationOf(string $type): array;
}
