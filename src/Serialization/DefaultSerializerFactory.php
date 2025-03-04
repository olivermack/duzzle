<?php

declare(strict_types=1);

namespace Duzzle\Serialization;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultSerializerFactory
{
    /**
     * @param ?callable(ObjectNormalizer $objectNormalizer): array<NormalizerInterface|DenormalizerInterface> $createNormalizers
     */
    public static function create(?callable $createNormalizers = null): Serializer
    {
        $classMetadataFactory = new ClassMetadataFactory(
            new AttributeLoader()
        );

        $extractor = new PropertyInfoExtractor(typeExtractors: [new PhpDocExtractor(), new ReflectionExtractor()]);
        $objectNormalizer = new ObjectNormalizer(
            classMetadataFactory: $classMetadataFactory,
            propertyTypeExtractor: $extractor,
            defaultContext: [
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]
        );

        $additionalNormalizers = is_callable($createNormalizers)
            ? $createNormalizers($objectNormalizer)
            : [];

        return new Serializer(
            [
                ...$additionalNormalizers,
                new ArrayDenormalizer(),
                $objectNormalizer,
                new DateTimeNormalizer(),
            ],
            [
                new JsonEncoder(),
                new XmlEncoder(),
            ]
        );
    }
}
