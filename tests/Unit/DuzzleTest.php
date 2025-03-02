<?php

declare(strict_types=1);

use Duzzle\Duzzle;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;

describe('Duzzle', function () {
    it('wraps the response always into a DuzzleResponse', function () {
        $httpClientMock = Mockery::mock(ClientInterface::class);
        $httpClientMock->expects('request')
            ->with('GET', '/foo', ['a' => 'b'])
            ->andReturn(Mockery::mock(ResponseInterface::class, [
                'getStatusCode' => 200,
                'getHeaders' => [],
                'getBody' => Utils::streamFor('foo'),
                'getProtocolVersion' => '1.1',
                'getReasonPhrase' => '',
            ]));
        $sut = new Duzzle($httpClientMock);

        expect($sut->request('GET', '/foo', ['a' => 'b']));
    });
});
