<?php

namespace PHPWhois2\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPWhois2\IpTools;

final class IpToolsTest extends TestCase
{
    #[DataProvider('validIpsProvider')]
    public function testValidIp($ip): void
    {
        $ipTools = new IpTools();
        self::assertTrue($ipTools->validIp($ip));
    }

    public static function validIpsProvider(): array
    {
        return [
            ['123.123.123.123'],
            ['1a80:1f45::ebb:12'],
        ];
    }

    #[DataProvider('invalidIpsProvider')]
    public function testInvalidIp($ip): void
    {
        $ipTools = new IpTools();
        self::assertFalse($ipTools->validIp($ip));
    }

    public static function invalidIpsProvider(): array
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
