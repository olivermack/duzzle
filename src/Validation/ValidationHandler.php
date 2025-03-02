<?php

declare(strict_types=1);

namespace Duzzle\Validation;

use Duzzle\DuzzleOptionsKeys;
use Duzzle\DuzzleTarget;
use Duzzle\Validation\Strategy\ValidationStrategyCollection;
use Duzzle\Validation\Strategy\ValidationStrategyInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ValidationHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private ValidationStrategyCollection $strategies,
        private array $defaultOptions = []
    ) {
    }

    public function handleInputValidation(array $requestOptions): void
    {
        $inputType = $requestOptions[DuzzleOptionsKeys::INPUT] ?? null;

        if (!empty($inputType) && ($inputStrategy = $this->getInputValidationStrategy($requestOptions)) instanceof ValidationStrategyInterface) {
            $violations = $this->validator->validate($inputType);
            $inputStrategy->handleViolations(DuzzleTarget::INPUT, $inputType, $violations);
        }
    }

    private function getInputValidationStrategy(array $options): ?ValidationStrategyInterface
    {
        $strategy = $options[DuzzleOptionsKeys::INPUT_VALIDATION]
            ?? $this->defaultOptions[DuzzleOptionsKeys::INPUT_VALIDATION]
            ?? null;

        if (is_string($strategy)) {
            $strategy = $this->strategies->get($strategy);
        }

        return $strategy;
    }

    public function handleOutputValidation(mixed $output, array $requestOptions): void
    {
        if (!empty($output) && ($outputStrategy = $this->getOutputValidationStrategy($requestOptions)) instanceof ValidationStrategyInterface) {
            $violations = $this->validator->validate($output);
            $outputStrategy->handleViolations(DuzzleTarget::OUTPUT, $output, $violations);
        }
    }

    private function getOutputValidationStrategy(array $options): ?ValidationStrategyInterface
    {
        $strategy = $options[DuzzleOptionsKeys::OUTPUT_VALIDATION]
            ?? $this->defaultOptions[DuzzleOptionsKeys::OUTPUT_VALIDATION]
            ?? null;

        if (is_string($strategy)) {
            $strategy = $this->strategies->get($strategy);
        }

        return $strategy instanceof ValidationStrategyInterface
            ? $strategy
            : null;
    }
}
