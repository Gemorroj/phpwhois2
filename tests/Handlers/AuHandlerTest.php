<?php

/**
 * @copyright Copyright (c) 2020 Joshua Smith
 * @license   See LICENSE file
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\AuHandler;

/**
 * AuHandlerTest.
 */
class AuHandlerTest extends AbstractHandler
{
    /**
     * @var AuHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AuHandler();
        $this->handler->deepWhois = false;
    }

    public function testParseTelstraDotComDotAu(): void
    {
        $query = 'telstra.com.au';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'telstra.com.au',
                'changed' => '2024-09-12',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
