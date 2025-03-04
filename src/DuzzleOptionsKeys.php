<?php

declare(strict_types=1);

namespace Duzzle;

interface DuzzleOptionsKeys
{
    public const string FORMAT = 'format';
    public const string INPUT = 'input';
    public const string INPUT_FORMAT = 'input_format';
    public const string OUTPUT = 'output';
    public const string OUTPUT_FORMAT = 'output_format';
    public const string ERROR = 'error';
    public const string ERROR_FORMAT = 'error_format';
    public const string NORMALIZATION_CONTEXT = 'normalization_context';
    public const string DENORMALIZATION_CONTEXT = 'denormalization_context';
    public const string INPUT_VALIDATION = 'input_validation';
    public const string OUTPUT_VALIDATION = 'output_validation';
}
