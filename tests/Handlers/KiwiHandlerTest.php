<?php

/**
 * @copyright Copyright (c) 2020 Joshua Smith
 * @license   See LICENSE file
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\KiwiHandler;

/**
 * KiwiHandlerTest.
 */
class KiwiHandlerTest extends AbstractHandler
{
    /**
     * @var KiwiHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new KiwiHandler();
        $this->handler->deepWhois = false;
    }

    public function testParseHelloDotKiwi(): void
    {
        $query = 'hello.kiwi';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'hello.kiwi',
                'created' => '2014-02-06',
                'changed' => '2022-04-14',
                'expires' => '2026-10-31',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseGoogleDotKiwi(): void
    {
        $query = 'google.kiwi';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'google.kiwi',
                'created' => '2014-03-25',
                'changed' => '2023-02-26',
                'expires' => '2024-03-25',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
