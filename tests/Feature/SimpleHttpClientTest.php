<?php

declare(strict_types=1);

use Duzzle\DuzzleBuilder;
use Duzzle\DuzzleOptionsKeys;
use Duzzle\Exception\RequestException;
use WireMock\Client\WireMock;

describe('SimpleHttp Client', function () {
    beforeEach(function () {
        $wireMockHost =  $_ENV['WIREMOCK_HOST'] ?? 'http://wiremock:8080/';
        $parsedWireMockHost = parse_url($wireMockHost);
        $this->wireMock = WireMock::create($parsedWireMockHost['host'], $parsedWireMockHost['port']);
        $this->httpClient = new GuzzleHttp\Client([
            'timeout' => 1.0,
            'base_uri' => $wireMockHost,
        ]);

        $this->duzzle = DuzzleBuilder::create([
            // avoid defaulting to json format
            DuzzleOptionsKeys::FORMAT => null,
        ])
            ->withGuzzleClient($this->httpClient)
            ->withDefaultSerializer()
            ->withDefaultValidator()
            ->build();
    });
    afterEach(function () {
        $this->wireMock->reset();
    });

    test('simple get 200', function () {
        $this->wireMock->stubFor(WireMock::get('/simple-get')->willReturn(WireMock::aResponse()->withBody('Some Text')));
        $res = $this->duzzle->request('GET', '/simple-get', [
        ]);
        expect($res)->toBe('Some Text');
    });

    test('simple 404', function () {
        $this->wireMock->stubFor(WireMock::get('/simple-404')->willReturn(WireMock::aResponse()->withStatus(404)));
        $this->duzzle->request('GET', '/simple-404');
    })->throws(RequestException::class);
});
