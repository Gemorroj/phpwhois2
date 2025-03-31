<?php

namespace PHPWhois2\Tests\Handler\Custom;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Handler\Custom\HuHandler;
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;

final class HuHandlerTest extends TestCase
{
    public function test(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('domain.hu');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(HuHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('Whois server 4.0 serving the hu ccTLD', $result->regrinfo['disclaimer'][0]);
        self::assertEquals('domain.hu', $result->regrinfo['domain']['name']);
        self::assertEquals('yes', $result->regrinfo['registered']);
    }
}
