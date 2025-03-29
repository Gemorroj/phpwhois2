<?php

/**
 * @copyright Copyright (c) 2020 Joshua Smith
 * @license   See LICENSE file
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\AtHandler;

/**
 * AtHandlerTest.
 */
class AtHandlerTest extends AbstractHandler
{
    /**
     * @var AtHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AtHandler();
        $this->handler->deepWhois = false;
    }

    public function testNicDotAt(): void
    {
        $query = 'nic.at';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                // 'name'    => 'nic.at',
                'changed' => '2020-04-27',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
