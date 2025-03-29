<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\BhHandler;
use PHPWhois2\WhoisClient;

final class BhHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new BhHandler(new WhoisClient(), false);
    }

    public function testParseNicDotBh(): void
    {
        $query = 'nic.bh';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'NIC.BH',
                'changed' => '2023-08-31',
                'created' => '2019-04-24',
                'expires' => '2029-04-24',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
