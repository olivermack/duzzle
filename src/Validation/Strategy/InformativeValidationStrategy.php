<?php

declare(strict_types=1);

namespace Duzzle\Validation\Strategy;

use Duzzle\DuzzleTarget;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class InformativeValidationStrategy implements ValidationStrategyInterface
{
    public function __construct(
        private ?LoggerInterface $logger,
        private readonly string $logLevel = LogLevel::WARNING,
    ) {
        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }
    }

    public function handleViolations(DuzzleTarget $target, mixed $value, ConstraintViolationListInterface $violations): void
    {
        if ($violations->count() > 0) {
            $this->logger->log($this->logLevel, $this->getLogMessage($target, $value, $violations), [
                'violations' => array_map(function (ConstraintViolationInterface $violation) {
                    return [
                        'property' => $violation->getPropertyPath(),
                        'message' => $violation->getMessage(),
                        'code' => $violation->getCode(),
                        'value' => $violation->getInvalidValue(),
                    ];
                }, iterator_to_array($violations)),
            ]);
        }
    }

    private function getLogMessage(DuzzleTarget $target, mixed $value, ConstraintViolationListInterface $violations): string
    {
        return sprintf(
            'Validation of %s `%s` failed with %d violation(s)',
            strtolower($target->value),
            get_debug_type($value),
            $violations->count(),
        );
    }
}
