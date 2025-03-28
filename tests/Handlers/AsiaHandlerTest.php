<?php

/**
 * @copyright Copyright (c) 2020 Joshua Smith
 * @license   See LICENSE file
 */

namespace Tests\Handlers;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use phpWhois\Handlers\AsiaHandler;

/**
 * AsiaHandlerTest.
 */
class AsiaHandlerTest extends AbstractHandler
{
    /**
     * @var AsiaHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AsiaHandler();
        $this->handler->deepWhois = false;
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

        Assert::assertArraySubset($expected, $actual['regrinfo'], 'Whois data may have changed');
        $this->assertArrayHasKey('rawdata', $actual);
        Assert::assertArraySubset($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
