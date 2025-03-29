<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\EuHandler;
use PHPWhois2\WhoisClient;

final class EuHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new EuHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotEu(): void
    {
        $query = 'google.eu';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            // 'domain'     => [
            //     'name'    => 'google.eu',
            //     'changed' => '2020-01-13',
            //     'created' => '2003-03-17',
            //     'expires' => '2022-03-17',
            // ],
            'registered' => 'yes',
        ];

        // self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'],'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseEuridDotEu(): void
    {
        $query = 'eurid.eu';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            // 'domain'     => [
            //     'name'    => 'eurid.eu',
            //     'changed' => '2020-08-03',
            //     'created' => '2003-03-10',
            //     'expires' => '2023-05-08',
            // ],
            'registered' => 'yes',
        ];

        // self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'],'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
