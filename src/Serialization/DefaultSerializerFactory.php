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
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
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

        $extractor = new PropertyInfoExtractor(typeExtractors: [new PhpDocExtractor(), new ReflectionExtractor()]);

        /*
         * Build the Serializer
         */
        return new Serializer(
            [
                new ArrayDenormalizer(),
                new ObjectNormalizer(
                    classMetadataFactory: $classMetadataFactory,
                    propertyTypeExtractor: $extractor,
                    defaultContext: [
                        ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                    ]
                ),
                new DateTimeNormalizer(),
            ],
            [
                new JsonEncoder(),
                new XmlEncoder(),
            ]
        );
    }
}
