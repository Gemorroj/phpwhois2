<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\DkHandler;
use PHPWhois2\WhoisClient;

final class DkHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new DkHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotDk(): void
    {
        $query = 'google.dk';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'domain' => 'google.dk',
                'registered' => '1999-01-10',
                'expires' => '2019-03-31',
                'status' => 'Active',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
