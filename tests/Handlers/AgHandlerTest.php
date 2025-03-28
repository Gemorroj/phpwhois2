<?php

/**
 * @license   See LICENSE file
 * @copyright Copyright (c) 2020 Joshua Smith
 */

namespace Tests\Handlers;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use phpWhois\Handlers\AgHandler;

/**
 * AgHandlerTest.
 */
class AgHandlerTest extends AbstractHandler
{
    /**
     * @var AgHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AgHandler();
        $this->handler->deepWhois = false;
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

        Assert::assertArraySubset($expected, $actual['regrinfo'], 'Whois data may have changed');
        $this->assertArrayHasKey('rawdata', $actual);
        Assert::assertArraySubset($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
