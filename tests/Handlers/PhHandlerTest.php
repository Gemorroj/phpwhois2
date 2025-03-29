<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\PhHandler;
use PHPWhois2\WhoisClient;

final class PhHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new PhHandler(new WhoisClient(), false);
    }

    public function testParseCityEscapeDotPh(): void
    {
        $query = 'cityescape.ph';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('cityescape.ph', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2024-01-30', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('1990-09-14', $actual['regrinfo']['domain']['created']);
        // self::assertEquals('2021-02-25', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }

    public function parseDotDotPh(): void
    {
        $query = 'dot.ph';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('dot.ph', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2016-07-25', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('2000-08-09', $actual['regrinfo']['domain']['created']);
        self::assertEquals('2025-08-09', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
