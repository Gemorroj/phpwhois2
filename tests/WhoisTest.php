<?php

namespace phpWhois\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use phpWhois\Whois;

class WhoisTest extends TestCase
{
    public function testWhois(): array
    {
        $whois = new Whois();
        $result = $whois->lookup('google.com');
        self::assertEquals('yes', $result['regrinfo']['registered']);

        return $result;
    }

    #[DataProvider('domainsProvider')]
    public function testQueryType($type, $domain): void
    {
        $whois = new Whois();
        $reflectionObj = new \ReflectionObject($whois);
        $reflectionMethod = $reflectionObj->getMethod('getQueryType');
        $actual = $reflectionMethod->invoke($whois, $domain);
        self::assertEquals($type, $actual);
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

    #[Depends('testWhois')]
    public function testShowHTML(array $data): void
    {
        $html = Whois::showHTML($data);
        self::assertStringStartsWith('<b>Domain Name: </b>GOOGLE.COM<br />', $html);
    }
}
