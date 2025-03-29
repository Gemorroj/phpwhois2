<?php

/**
 * @license   See LICENSE file
 * @copyright Copyright (c) 2020 Joshua Smith
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\AgHandler;
use phpWhois\WhoisClient;

final class AgHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AgHandler(new WhoisClient(), false);
    }

    public function testParseNicDotAg(): void
    {
        $query = 'nic.ag';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'nic.ag',
                'changed' => '2024-09-23',
                'created' => '1998-05-02',
                'expires' => '2025-05-02',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
