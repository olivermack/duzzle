<?php

declare(strict_types=1);

namespace Duzzle\Validation;

use Duzzle\DuzzleInterface;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\DuzzleTarget;
use Duzzle\Validation\Strategy\ValidationStrategyCollection;
use Duzzle\Validation\Strategy\ValidationStrategyInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ValidationDecorator implements DuzzleInterface
{
    public const string INPUT_VALIDATION = 'input_validation';

    public const string OUTPUT_VALIDATION = 'output_validation';

    public function __construct(
        private DuzzleInterface $decorated,
        private ValidatorInterface $validator,
        private ValidationStrategyCollection $strategies,
        private array $defaultOptions = []
    ) {
    }

    public function request(string $method, string $url, array $options = []): mixed
    {
        $inputType = $options[DuzzleOptionsKeys::INPUT] ?? null;

        if (!empty($inputType) && ($inputStrategy = $this->getInputValidationStrategy($options)) instanceof ValidationStrategyInterface) {
            $violations = $this->validator->validate($inputType);
            $inputStrategy->handleViolations(DuzzleTarget::INPUT, $inputType, $violations);
        }

        $res = $this->decorated->request($method, $url, array_merge($this->defaultOptions, $options));

        if (!empty($res) && ($outputStrategy = $this->getOutputValidationStrategy($options)) instanceof ValidationStrategyInterface) {
            $violations = $this->validator->validate($res);
            $outputStrategy->handleViolations(DuzzleTarget::OUTPUT, $res, $violations);
        }

        return $res;
    }

    private function getInputValidationStrategy(array $options): ?ValidationStrategyInterface
    {
        $strategy = $options[self::INPUT_VALIDATION]
            ?? $this->defaultOptions[self::INPUT_VALIDATION]
            ?? null;

        if (is_string($strategy)) {
            $strategy = $this->strategies->get($strategy);
        }

        return $strategy;
    }

    private function getOutputValidationStrategy(array $options): ?ValidationStrategyInterface
    {
        $strategy = $options[self::OUTPUT_VALIDATION]
            ?? $this->defaultOptions[self::OUTPUT_VALIDATION]
            ?? null;

        if (is_string($strategy)) {
            $strategy = $this->strategies->get($strategy);
        }

        return $strategy instanceof ValidationStrategyInterface
            ? $strategy
            : null;
    }
}
