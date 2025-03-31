<?php

namespace PHPWhois2\Tests\Handler\Custom;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Handler\Custom\AmHandler;
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;

final class AmHandlerTest extends TestCase
{
    public function test(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('isoc.am');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(AmHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('isoc.am', $result->regrinfo['domain']['name']);
        self::assertEquals('2000-01-01', $result->regrinfo['domain']['created']);
        self::assertEquals('Internet Society NGO', $result->regrinfo['owner']['name']);
    }
}
