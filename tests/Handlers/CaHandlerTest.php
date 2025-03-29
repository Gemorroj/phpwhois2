<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\CaHandler;
use PHPWhois2\WhoisClient;

final class CaHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CaHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotCa(): void
    {
        $query = 'google.ca';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('google.ca', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2020-04-28', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('2000-10-04', $actual['regrinfo']['domain']['created']);
        self::assertEquals('2021-04-28', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }

    public function testParseCiraDotCa(): void
    {
        $query = 'cira.ca';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('CIRA', $actual['regyinfo']['registrar']);
        self::assertEquals('https://www.cira.ca/', $actual['regyinfo']['referrer']);
        self::assertEquals('no', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
