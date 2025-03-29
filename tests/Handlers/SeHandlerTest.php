<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\SeHandler;
use PHPWhois2\WhoisClient;

final class SeHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new SeHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotDk(): void
    {
        $query = 'google.se';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'google.se',
                'created' => '2003-08-27',
                'changed' => '2017-09-18',
                'expires' => '2018-10-20',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
