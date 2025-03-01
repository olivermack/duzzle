<?php

declare(strict_types=1);

arch()->preset()->security();
arch()->preset()->php();

arch()
    ->expect('Duzzle')
    ->toUseStrictTypes()
    ->toUseStrictEquality()
    ->not->toUse(['dd', 'dump', 'exit', 'echo']);

arch()
    ->expect('Duzzle')
    ->toUseStrictEquality();

arch()
    ->expect('Duzzle\Exception')
    ->toImplement('Duzzle\Exception\DuzzleExceptionInterface');

arch()
    ->expect('Duzzle\Exception')
    ->toHaveSuffix('Exception')
    ->ignoring('Duzzle\Exception\DuzzleExceptionInterface');

arch()
    ->expect('Duzzle\Serialization')
    ->not->toUse(['Duzzle\Validation']);

arch()
    ->expect('Duzzle\Validation')
    ->not->toUse(['Duzzle\Serialization']);
