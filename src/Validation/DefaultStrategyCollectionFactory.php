<?php

declare(strict_types=1);

namespace Duzzle\Validation;

use Duzzle\Validation\Strategy\BlockingValidationStrategy;
use Duzzle\Validation\Strategy\DefaultStrategyKey;
use Duzzle\Validation\Strategy\InformativeValidationStrategy;
use Duzzle\Validation\Strategy\NoopValidationStrategy;
use Duzzle\Validation\Strategy\ValidationStrategyCollection;
use Psr\Log\LoggerInterface;

class DefaultStrategyCollectionFactory
{
    public static function create(?LoggerInterface $logger = null): ValidationStrategyCollection
    {
        return new ValidationStrategyCollection([
            DefaultStrategyKey::NOOP->value => new NoopValidationStrategy(),
            DefaultStrategyKey::BLOCKING->value => new BlockingValidationStrategy(),
            DefaultStrategyKey::INFORMATIVE->value => new InformativeValidationStrategy($logger),
        ]);
    }
}
