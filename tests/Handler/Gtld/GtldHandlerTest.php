<?php

namespace PHPWhois2\Tests\Handler\Gtld;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Handler\Gtld\GtldHandler;
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;

final class GtldHandlerTest extends TestCase
{
    public function testJoker(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('joker.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        self::assertEquals('CSL Computer Service Langenbach GmbH d/b/a joker.com', $result->regyinfo['registrar']);
    }

    public function testAlldomains(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('alldomains.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        self::assertEquals('Domain.com, LLC', $result->regyinfo['registrar']);
    }

    public function testAscio(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('ascio.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('Ascio Technologies, Inc. Danmark - Filial af Ascio technologies, Inc. USA', $result->regyinfo['registrar']);
    }

    public function testCorporatedomains(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('corporatedomains.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('CSC Corporate Domains, Inc.', $result->regyinfo['registrar']);
    }

    public function testDirectnic(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('directnic.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('DNC Holdings, Inc.', $result->regyinfo['registrar']);
    }

    public function testDomaindiscover(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('domaindiscover.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('DomainSpot LLC', $result->regyinfo['registrar']);
    }

    public function testDomainpeople(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('domainpeople.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('DomainPeople, Inc.', $result->regyinfo['registrar']);
    }

    public function testDreamhost(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('dreamhost.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('DreamHost, LLC', $result->regyinfo['registrar']);
    }

    public function testEnom(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('enom.com');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(GtldHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('eNom, LLC', $result->regyinfo['registrar']);
    }
}
