<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\AeHandler;
use PHPWhois2\WhoisClient;

final class AeHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AeHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotAe(): void
    {
        $query = 'google.ae';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'google.ae',
                'sponsor' => 'MarkMonitor',
                'status' => 'clientUpdateProhibited',
            ],
            'owner' => [
                'name' => 'Domain Administrator',
            ],
            'tech' => [
                'name' => 'Domain Administrator',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['owner'], $actual['regrinfo']['owner'], $expected['owner'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['tech'], $actual['regrinfo']['tech'], $expected['tech'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseNicDotAe(): void
    {
        $query = 'nic.ae';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'nic.ae',
                'sponsor' => 'Etisalat',
                'status' => 'clientUpdateProhibited',
            ],
            'owner' => [
                'name' => 'Emirates Telecommunications Corporation - Etisalat',
            ],
            'tech' => [
                'name' => 'Emirates Telecommunications Corporation - Etisalat',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['owner'], $actual['regrinfo']['owner'], $expected['owner'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['tech'], $actual['regrinfo']['tech'], $expected['tech'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseAedaDotAe(): void
    {
        $query = 'aeda.ae';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'aeda.ae',
                'sponsor' => 'aeDA Regulator',
                'status' => 'ok',
            ],
            'owner' => [
                'name' => 'Telecommunication Regulatory Authority',
            ],
            'tech' => [
                'name' => '.ae Domain Administration',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['owner'], $actual['regrinfo']['owner'], $expected['owner'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['tech'], $actual['regrinfo']['tech'], $expected['tech'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
