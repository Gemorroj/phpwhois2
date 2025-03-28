<?php

namespace Tests;

use phpWhois\IpTools;

class IpToolsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider validIpsProvider
     */
    public function testValidIp($ip): void
    {
        $ipTools = new IpTools();
        $this->assertTrue($ipTools->validIp($ip));
    }

    public static function validIpsProvider()
    {
        return [
            ['123.123.123.123'],
            ['1a80:1f45::ebb:12'],
        ];
    }

    /**
     * @dataProvider invalidIpsProvider
     */
    public function testInvalidIp($ip): void
    {
        $ipTools = new IpTools();
        $this->assertFalse($ipTools->validIp($ip));
    }

    public static function invalidIpsProvider()
    {
        return [
            [''],
            ['169.254.255.200'],
            ['172.17.255.100'],
            ['123.a15.255.100'],
            ['fd80::1'],
            ['fc80:19c::1'],
            ['1a80:1f45::ebm:12'],
            ['[1a80:1f45::ebb:12]'],
        ];
    }
}
