<?php

namespace phpWhois\Tests;

class IpTest extends \PHPUnit\Framework\TestCase
{
    public function testParseIp(): void
    {
        $whois = new \phpWhois\Whois();
        $result = $whois->lookup('216.58.209.195');
        self::assertIsArray($result);
        self::assertArrayHasKey('regrinfo', $result);
        self::assertArrayHasKey('rawdata', $result);
        self::assertArrayHasKey('rawdata', $result);
        self::assertEquals('GOOGLE', $result['regrinfo']['network']['name']);
        self::assertEquals('American Registry for Internet Numbers (ARIN)', $result['regyinfo']['registrar']);
    }
}
