<?php

declare(strict_types=1);

use Duzzle\DuzzleInterface;
use Duzzle\Serialization\SerializationDecorator;
use Symfony\Component\Serializer\Serializer;

describe('SerializationDecorator', function () {
    it('bubbles if invalid response', function () {
        $nested = Mockery::mock(DuzzleInterface::class);
        $nested->expects()->request('GET', '/foo', []);

        $serializer = Mockery::mock(Serializer::class);
        $sut = new SerializationDecorator($nested, $serializer);

        $sut->request('GET', '/foo');
    })->throws(Duzzle\Exception\RuntimeException::class);
});
