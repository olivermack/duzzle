<?php

declare(strict_types=1);

namespace Duzzle\Validation\Strategy;

enum DefaultStrategyKey: string
{
    case NOOP = 'noop';
    case BLOCKING = 'blocking';
    case INFORMATIVE = 'informative';
}
