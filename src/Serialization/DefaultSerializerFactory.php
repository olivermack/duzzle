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
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultSerializerFactory
{
    public static function create(): Serializer
    {
        /**
         * Create a metadata factory that reads PHP 8 attributes.
         */
        $classMetadataFactory = new ClassMetadataFactory(
            new AttributeLoader()
        );

        /**
         * Create a PropertyInfo extractor that can interpret both:
         *    - @var docblocks (PhpDocExtractor)
         *    - Reflection (ReflectionExtractor) for native type hints (including property promotion)
         */
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $propertyInfoExtractor = new PropertyInfoExtractor(
            // property list extractors (optional), e.g. []
            [],
            // property type extractors, in priority order:
            [$phpDocExtractor, $reflectionExtractor]
        );

        /**
         * Create the ObjectNormalizer:
         *    - Uses our ClassMetadataFactory (so it knows about #[Groups], #[SerializedName], etc.)
         *    - Uses the PropertyInfo extractor for type resolution
         */
        $objectNormalizer = new ObjectNormalizer(
            $classMetadataFactory,
            nameConverter: new MetadataAwareNameConverter($classMetadataFactory),
            propertyInfoExtractor: $propertyInfoExtractor
        );

        /**
         * Combine Normalizers (e.g., DateTimeNormalizer for \DateTimeInterface) and Encoders (JSON + XML).
         */
        $normalizers = [
            new DateTimeNormalizer(),
            $objectNormalizer,
        ];

        $encoders = [
            new JsonEncoder(),
            new XmlEncoder(),
        ];

        /**
         * Build the Serializer
         */
        return new Serializer($normalizers, $encoders);
    }
}
