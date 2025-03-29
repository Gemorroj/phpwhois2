<?php

/**
 * @license   See LICENSE file
 * @copyright Copyright (c) 2020 Joshua Smith
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\AmHandler;

/**
 * AmHandlerTest.
 */
class AmHandlerTest extends AbstractHandler
{
    /**
     * @var AmHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AmHandler();
        $this->handler->deepWhois = false;
    }

    public function testParseIsocDotAm(): void
    {
        $query = 'isoc.am';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'isoc.am',
                'created' => '2000-01-01',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
