<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\Gtld\GtldHandler;
use PHPWhois2\WhoisClient;

final class GtldHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new GtldHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotCom(): void
    {
        $query = 'google.com';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'GOOGLE.COM',
                'changed' => '2011-07-20',
                'created' => '1997-09-15',
                'expires' => '2020-09-14',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
