<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\OrgHandler;
use PHPWhois2\WhoisClient;

final class OrgHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new OrgHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotOrg(): void
    {
        $query = 'google.org';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'GOOGLE.ORG',
                'changed' => '2017-09-18',
                'created' => '1998-10-21',
                'expires' => '2018-10-20',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseDatesProperly(): void
    {
        $query = 'scottishrecoveryconsortium.org';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'SCOTTISHRECOVERYCONSORTIUM.ORG',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');

        self::assertEquals('2020-01-13', $actual['regrinfo']['domain']['changed'], 'Incorrect change date');
        self::assertEquals('2012-10-01', $actual['regrinfo']['domain']['created'], 'Incorrect created date');
        self::assertEquals('2020-10-01', $actual['regrinfo']['domain']['expires'], 'Incorrect expiration date');
    }
}
