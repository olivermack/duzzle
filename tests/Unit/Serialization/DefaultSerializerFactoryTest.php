<?php

declare(strict_types=1);

use Duzzle\Serialization\DefaultSerializerFactory;
use Duzzle\Tests\Fixtures\TestNormalizer;
use Duzzle\Tests\Fixtures\Todo;
use Symfony\Component\Serializer\Serializer;

describe('DefaultSerializerFactoryTest', function () {
    it('builds a Serializer', function () {
        expect(DefaultSerializerFactory::create())->toBeInstanceOf(Serializer::class);
    });

    it('default serializer serializes DTO to json', function () {
        $sut = DefaultSerializerFactory::create();
        $obj = new Todo();
        $obj->id = 2;
        $obj->userId = 10;
        $obj->title = 'title';
        $obj->completed = true;
        $res = $sut->serialize($obj, 'json');
        expect($res)->toEqual('{"id":2,"userId":10,"title":"title","completed":true}');
    });

    it('default serializer deserializes json to DTO', function () {
        $sut = DefaultSerializerFactory::create();
        $obj = new Todo();
        $obj->id = 2;
        $obj->userId = 10;
        $obj->title = 'title';
        $obj->completed = true;
        $res = $sut->deserialize('{"id":2,"userId":10,"title":"title","completed":true}', Todo::class, 'json');
        expect($res)->toEqual($obj);
    });

    it('can add additional normalizers', function () {
        $sut = DefaultSerializerFactory::create(function () {
            return [new TestNormalizer()];
        });

        $res = $sut->serialize('TestNormalizerInput', 'json');
        expect($res)->toBe('"TestNormalizerInput normalized as json"');
    });
});
