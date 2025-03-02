<?php

declare(strict_types=1);

namespace Duzzle;

use Duzzle\Serialization\ContextBuilderInterface;
use Duzzle\Serialization\DefaultSerializerFactory;
use Duzzle\Serialization\SerializationHandler;
use Duzzle\Serialization\SerializationMiddleware;
use Duzzle\Validation\DefaultStrategyCollectionFactory;
use Duzzle\Validation\DefaultValidatorFactory;
use Duzzle\Validation\Strategy\ValidationStrategyCollection;
use Duzzle\Validation\ValidationHandler;
use Duzzle\Validation\ValidationMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DuzzleBuilder
{
    private ?ClientInterface $httpClient = null;
    private ?Serializer $serializer = null;
    private ?ContextBuilderInterface $serializationContextBuilder = null;
    private ?LoggerInterface $logger = null;
    private ?ValidatorInterface $validator = null;
    private ?ValidationStrategyCollection $validationStrategyCollection = null;

    /**
     * @param array<string, mixed> $config
     */
    private function __construct(private array $config = [])
    {
        $this->config = array_merge([DuzzleOptionsKeys::FORMAT => 'json'], $this->config);
        $this->logger = new NullLogger();
    }

    /**
     * @param array<string, mixed> $config
     */
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
        $handlerStack = HandlerStack::create();

        if ($this->serializer instanceof SerializerInterface) {
            // it is vital to add the serialization middleware "before" the prepare_body middleware
            // of guzzle is applied in order to allow setting the request payload
            $handlerStack->before('prepare_body', new SerializationMiddleware(
                new SerializationHandler($this->serializer, $this->serializationContextBuilder)
            ), 'duzzle_serialization');
        }

        if ($this->validator instanceof ValidatorInterface) {
            $strategyCollection = $this->validationStrategyCollection ?? DefaultStrategyCollectionFactory::create($this->logger);
            $handlerStack->before(
                'duzzle_serialization',
                new ValidationMiddleware(
                    new ValidationHandler($this->validator, $strategyCollection, $this->config)
                ),
                'duzzle_validation'
            );
        }

        return new Duzzle(
            $this->httpClient ?? new Client([
                ...$this->config,
                'handler' => $handlerStack,
            ])
        );
    }
}
