<?php

namespace PHPWhois2\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use PHPWhois2\Data;
use PHPWhois2\Whois;

final class WhoisTest extends TestCase
{
    public function testDomainLookup(): Data
    {
        $whois = new Whois();
        $result = $whois->lookup('google.com');
        self::assertNotEmpty($result->rawData);
        self::assertEquals('yes', $result->regrinfo['registered']);

        return $result;
    }

    public function testDomainLookupFail(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('google.comqwerty');

        self::assertEmpty($result->regyinfo);
        self::assertEmpty($result->rawData);
        self::assertEquals('google.comqwerty', $result->regrinfo['domain']['name']);
        self::assertEquals('unknown', $result->regrinfo['registered']);
        self::assertEquals('google.comqwerty domain is not supported', $result->errstr[0]);
    }

    public function testIpLookup(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('AS12345');
        self::assertEquals('yes', $result->regrinfo['registered']);
        self::assertEquals('RIPE NCC ASN block', $result->regrinfo['owner']['organization']);
    }

    public function testIpLookupFail(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('AS123456789');

        self::assertEquals('as123456789', $result->regrinfo['domain']['name']);
        self::assertEquals('unknown', $result->regrinfo['registered']);
    }

    public function testAsLookup(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('216.58.209.195');
        self::assertEquals('GOOGLE', $result->regrinfo['network']['name']);
        self::assertEquals('American Registry for Internet Numbers (ARIN)', $result->regyinfo['registrar']);
    }

    public function testAsLookupFail(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('256.256.256.256');

        self::assertEmpty($result->regyinfo);
        self::assertEmpty($result->rawData);
        self::assertEquals('256.256.256.256', $result->regrinfo['domain']['name']);
        self::assertEquals('unknown', $result->regrinfo['registered']);
        self::assertEquals('256.256.256.256 domain is not supported', $result->errstr[0]);
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

    #[Depends('testDomainLookup')]
    public function testShowHTML(Data $data): void
    {
        $html = Whois::showHTML($data);
        self::assertStringStartsWith('<b>Domain Name: </b>GOOGLE.COM<br />', $html);
    }
}
