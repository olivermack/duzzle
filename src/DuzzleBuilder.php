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
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DuzzleBuilder
{
    public const string CONFIG_KEY_HANDLER = 'handler';
    public const string PREPARE_BODY_MIDDLEWARE_NAME = 'prepare_body';
    public const string SERIALIZATION_MIDDLEWARE_NAME = 'serialization';
    public const string VALIDATION_MIDDLEWARE_NAME = 'validation';

    private ?ClientInterface $httpClient = null;
    private ?Serializer $serializer = null;
    private ?ContextBuilderInterface $serializationContextBuilder = null;
    private LoggerInterface $logger;
    private ?ValidatorInterface $validator = null;
    private ?ValidationStrategyCollection $validationStrategyCollection = null;

    private ?HandlerStack $handlerStack = null;

    /**
     * @param array<string, mixed> $config
     */
    private function __construct(private array $config = [])
    {
        $this->config = array_merge([DuzzleOptionsKeys::FORMAT => 'json'], $this->config);
        $this->logger = new NullLogger();
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function create(array $options = []): self
    {
        return new self($options);
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

    /**
     * @param ?callable(ObjectNormalizer $objectNormalizer): array<NormalizerInterface|DenormalizerInterface> $createNormalizers
     *
     * @return $this
     */
    public function withDefaultSerializer(?callable $createNormalizers = null): self
    {
        $this->withSerializer(DefaultSerializerFactory::create($createNormalizers));

        return $this;
    }

    public function withSerializer(Serializer $serializer, ?ContextBuilderInterface $contextBuilder = null): self
    {
        $this->serializer = $serializer;

        return null === $contextBuilder
            ? $this
            : $this->withSerializationContextBuilder($contextBuilder);
    }

    public function withSerializationContextBuilder(ContextBuilderInterface $contextBuilder): self
    {
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

    public function withHandlerStack(HandlerStack $handlerStack): self
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    public function build(): DuzzleInterface
    {
        $handlerStack = $this->handlerStack ?? $this->config['handler'] ?? HandlerStack::create();

        if ($this->serializer instanceof SerializerInterface) {
            // it is vital to add the serialization middleware "before" the prepare_body middleware
            // of guzzle is applied in order to allow setting the request payload
            $handlerStack->before(self::PREPARE_BODY_MIDDLEWARE_NAME, new SerializationMiddleware(
                new SerializationHandler($this->serializer, $this->serializationContextBuilder)
            ), self::SERIALIZATION_MIDDLEWARE_NAME);
        }

        if ($this->validator instanceof ValidatorInterface) {
            $strategyCollection = $this->validationStrategyCollection ?? DefaultStrategyCollectionFactory::create($this->logger);
            $handlerStack->before(
                self::SERIALIZATION_MIDDLEWARE_NAME,
                new ValidationMiddleware(
                    new ValidationHandler($this->validator, $strategyCollection, $this->config)
                ),
                self::VALIDATION_MIDDLEWARE_NAME
            );
        }

        return new Duzzle(
            $this->httpClient ?? new Client([
                ...$this->config,
                self::CONFIG_KEY_HANDLER => $handlerStack,
            ])
        );
    }
}
