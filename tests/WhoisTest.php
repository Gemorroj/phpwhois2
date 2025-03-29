<?php

namespace phpWhois\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use phpWhois\Whois;

class WhoisTest extends \PHPUnit\Framework\TestCase
{
    public function testWhois(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('google.com');
        $this->assertEquals('yes', $result['regrinfo']['registered']);
    }

    #[DataProvider('domainsProvider')]
    public function testQueryType($type, $domain): void
    {
        $whois = new Whois();
        $reflectionObj = new \ReflectionObject($whois);
        $reflectionMethod = $reflectionObj->getMethod('getQueryType');
        $actual = $reflectionMethod->invoke($whois, $domain);
        $this->assertEquals($type, $actual);
    }

    public static function domainsProvider(): array
    {
        return [
            [Whois::QTYPE_DOMAIN,  'www.google.com'],
            [Whois::QTYPE_DOMAIN,  'президент.рф'],
            [Whois::QTYPE_IPV4,    '212.212.12.12'],
            [Whois::QTYPE_UNKNOWN, '127.0.0.1'],
            [Whois::QTYPE_IPV6,    '1a80:1f45::ebb:12'],
            [Whois::QTYPE_UNKNOWN, 'fc80:19c::1'],
            [Whois::QTYPE_AS,      'ABCD_EF-GH:IJK'],
        ];
    }
}
