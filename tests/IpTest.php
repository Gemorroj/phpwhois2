<?php

namespace PHPWhois2\Tests;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Whois;

class IpTest extends TestCase
{
    public function testParseIp(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('216.58.209.195');
        self::assertIsArray($result);
        self::assertArrayHasKey('regrinfo', $result);
        self::assertArrayHasKey('rawdata', $result);
        self::assertArrayHasKey('rawdata', $result);
        self::assertEquals('GOOGLE', $result['regrinfo']['network']['name']);
        self::assertEquals('American Registry for Internet Numbers (ARIN)', $result['regyinfo']['registrar']);
    }
}
