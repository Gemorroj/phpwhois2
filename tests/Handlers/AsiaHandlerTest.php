<?php

/**
 * @copyright Copyright (c) 2020 Joshua Smith
 * @license   See LICENSE file
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\AsiaHandler;
use phpWhois\WhoisClient;

final class AsiaHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AsiaHandler(new WhoisClient(), false);
    }

    public function testParseNicDotAsia(): void
    {
        $query = 'nic.asia';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'nic.asia',
                'changed' => '2023-02-28',
                'created' => '2020-02-28',
                'expires' => '2024-02-28',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
