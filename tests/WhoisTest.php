<?php

namespace PHPWhois2\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use PHPWhois2\Whois;

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
        $whois = new Whois();
        $reflectionObj = new \ReflectionObject($whois);
        $reflectionConstantDomain = $reflectionObj->getReflectionConstant('QTYPE_DOMAIN');
        $reflectionConstantIpv4 = $reflectionObj->getReflectionConstant('QTYPE_IPV4');
        $reflectionConstantIpv6 = $reflectionObj->getReflectionConstant('QTYPE_IPV6');
        $reflectionConstantUnknown = $reflectionObj->getReflectionConstant('QTYPE_UNKNOWN');
        $reflectionConstantAs = $reflectionObj->getReflectionConstant('QTYPE_AS');

        return [
            [$reflectionConstantDomain->getValue(),  'www.google.com'],
            [$reflectionConstantDomain->getValue(),  'президент.рф'],
            [$reflectionConstantIpv4->getValue(),    '212.212.12.12'],
            [$reflectionConstantUnknown->getValue(), '127.0.0.1'],
            [$reflectionConstantIpv6->getValue(),    '1a80:1f45::ebb:12'],
            [$reflectionConstantUnknown->getValue(), 'fc80:19c::1'],
            [$reflectionConstantAs->getValue(),      'ABCD_EF-GH:IJK'],
        ];
    }

    #[Depends('testWhois')]
    public function testShowHTML(array $data): void
    {
        $html = Whois::showHTML($data);
        self::assertStringStartsWith('<b>Domain Name: </b>GOOGLE.COM<br />', $html);
    }
}
