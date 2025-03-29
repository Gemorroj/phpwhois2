<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\ClHandler;
use PHPWhois2\WhoisClient;

final class ClHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ClHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotCl(): void
    {
        $query = 'google.cl';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'google.cl',
                // 'changed' => '2020-01-13',
                'created' => '2002-10-22',
                'expires' => '2025-11-20',
            ],
            // 'registered' => 'yes', // Currently broken
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        // self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
