<?php

namespace PHPWhois2\Tests\Handler;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Handler\CommonHandler;
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;

final class CommonHandlerTest extends TestCase
{
    public function testAe(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('google.ae');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(CommonHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('google.ae', $result->regrinfo['domain']['name']);
        self::assertEquals('markmonitor', $result->regrinfo['domain']['sponsor']);
        self::assertEquals('Google LLC', $result->regrinfo['owner']['organization']);
    }
}
