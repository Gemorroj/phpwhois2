<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\LondonHandler;
use PHPWhois2\WhoisClient;

final class LondonHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new LondonHandler(new WhoisClient(), false);
    }

    public function testParseNicDotLondon(): void
    {
        $query = 'nic.london';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('nic.london', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2020-02-25', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('2014-02-25', $actual['regrinfo']['domain']['created']);
        self::assertEquals('2021-02-25', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }

    public function testParseDomainsDotLondon(): void
    {
        $query = 'domains.london';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('domains.london', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2020-02-16', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('2015-02-23', $actual['regrinfo']['domain']['created']);
        self::assertEquals('2021-02-23', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
