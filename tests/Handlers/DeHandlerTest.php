<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\DeHandler;
use PHPWhois2\WhoisClient;

final class DeHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new DeHandler(new WhoisClient(), false);
    }

    public function testParse4EverDotDe(): void
    {
        $query = '4ever.de';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => '4ever.de',
                'nserver' => [
                    0 => 'ns1.detebe.org',
                    1 => 'ns2.detebe.org',
                    2 => 'ns.4ever.de 193.200.137.137',
                    3 => 'ns.does.not-exist.de',
                ],
                'status' => 'connect',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain']['nserver'], $actual['regrinfo']['domain']['nserver'], $expected['domain']['nserver'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['name'], $actual['regrinfo']['domain']['name'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['status'], $actual['regrinfo']['domain']['status'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseGoogleDotDe(): void
    {
        $query = 'google.de';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'google.de',
                'nserver' => [
                    0 => 'ns1.google.com',
                    1 => 'ns2.google.com',
                    2 => 'ns3.google.com',
                    3 => 'ns4.google.com',
                ],
                'status' => 'connect',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain']['nserver'], $actual['regrinfo']['domain']['nserver'], $expected['domain']['nserver'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['name'], $actual['regrinfo']['domain']['name'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['status'], $actual['regrinfo']['domain']['status'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseDenicDotDe(): void
    {
        $query = 'denic.de';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'denic.de',
                'nserver' => [
                    0 => 'ns1.denic.de 77.67.63.106 2001:668:1f:11:0:0:0:106',
                    1 => 'ns2.denic.de 81.91.164.6 2a02:568:0:2:0:0:0:54',
                    2 => 'ns3.denic.de 195.243.137.27 2003:8:14:0:0:0:0:106',
                    3 => 'ns4.denic.net',
                ],
                'status' => 'connect',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain']['nserver'], $actual['regrinfo']['domain']['nserver'], $expected['domain']['nserver'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['name'], $actual['regrinfo']['domain']['name'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['status'], $actual['regrinfo']['domain']['status'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseDomainInConnectStatus(): void
    {
        $query = 'humblebundle.de';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'humblebundle.de',
                'nserver' => [
                    0 => 'ns1.redirectdom.com',
                    1 => 'ns2.redirectdom.com',
                ],
                'status' => 'connect',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain']['nserver'], $actual['regrinfo']['domain']['nserver'], $expected['domain']['nserver'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['name'], $actual['regrinfo']['domain']['name'], 'Whois data may have changed');
        self::assertEquals($expected['domain']['status'], $actual['regrinfo']['domain']['status'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseDomainInFreeStatus(): void
    {
        $query = 'a2ba91bff88c6983f6af010c41236206df64001d.de';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'a2ba91bff88c6983f6af010c41236206df64001d.de',
            ],
            'registered' => 'no',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
