<?php

/**
 * @license   See LICENSE file
 * @copyright Copyright (c) 2020 Joshua Smith
 */

namespace Tests\Handlers;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use phpWhois\Handlers\AeroHandler;

/**
 * AeroHandlerTest.
 */
class AeroHandlerTest extends AbstractHandler
{
    /**
     * @var AeroHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AeroHandler();
        $this->handler->deepWhois = false;
    }

    public function testParseNicDotAero(): void
    {
        $query = 'nic.aero';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'nic.aero',
                'changed' => '2023-03-08',
                'created' => '2002-03-08',
                'expires' => '2024-03-08',
            ],
            'registered' => 'yes', // Currently broken
        ];

        Assert::assertArraySubset($expected, $actual['regrinfo'], 'Whois data may have changed');
        $this->assertArrayHasKey('rawdata', $actual);
        Assert::assertArraySubset($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
