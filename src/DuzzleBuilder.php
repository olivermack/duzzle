<?php

declare(strict_types=1);

namespace Duzzle;

use Duzzle\Serialization\ContextBuilderInterface;
use Duzzle\Serialization\DefaultSerializerFactory;
use Duzzle\Serialization\SerializationDecorator;
use Duzzle\Validation\DefaultStrategyCollectionFactory;
use Duzzle\Validation\DefaultValidatorFactory;
use Duzzle\Validation\Strategy\ValidationStrategyCollection;
use Duzzle\Validation\ValidationDecorator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DuzzleBuilder
{
    private ?ClientInterface $httpClient = null;
    private ?SerializerInterface $serializer = null;
    private ?ContextBuilderInterface $serializationContextBuilder = null;
    private ?LoggerInterface $logger = null;
    private ?ValidatorInterface $validator = null;
    private ?ValidationStrategyCollection $validationStrategyCollection = null;

    private function __construct(private array $config = [])
    {
        $this->config = array_merge([DuzzleOptionsKeys::FORMAT => 'json'], $this->config);
        $this->logger = new NullLogger();
    }

    public static function create(array $config = []): self
    {
        return new self($config);
    }

    public function withGuzzleClient(ClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function withDefaultSerializer(): self
    {
        return $this->withSerializer(DefaultSerializerFactory::create());
    }

    public function withSerializer(Serializer $serializer, ?ContextBuilderInterface $contextBuilder = null): self
    {
        $this->serializer = $serializer;
        $this->serializationContextBuilder = $contextBuilder;

        return $this;
    }

    public function withDefaultValidator(): self
    {
        return $this->withValidator(DefaultValidatorFactory::create());
    }

    public function withValidator(ValidatorInterface $validator, ?ValidationStrategyCollection $strategies = null): self
    {
        $this->validator = $validator;
        $this->validationStrategyCollection = $strategies;

        return $this;
    }

    public function build(): DuzzleInterface
    {
        $duzzle = new Duzzle(
            $this->httpClient ?? new Client($this->config)
        );

        if ($this->serializer) {
            $duzzle = new SerializationDecorator($duzzle, $this->serializer, $this->serializationContextBuilder, $this->config);
        }

        if ($this->validator) {
            $strategyCollection = $this->validationStrategyCollection ?? DefaultStrategyCollectionFactory::create($this->logger);
            $duzzle = new ValidationDecorator($duzzle, $this->validator, $strategyCollection, $this->config);
        }

        return $duzzle;
    }
}
