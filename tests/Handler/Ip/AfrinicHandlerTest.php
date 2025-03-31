<?php

namespace PHPWhois2\Tests\Handler\Ip;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Handler\Ip\AfrinicHandler;
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;

final class AfrinicHandlerTest extends TestCase
{
    public function test(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('102.130.135.102');
        // $result = $whois->lookup('AS33764');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(AfrinicHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        // \var_dump($result->regyinfo);
        self::assertEquals('yes', $result->regrinfo['registered']);
        self::assertEquals('Gerrit Victor', $result->regrinfo['admin']['name']);
        self::assertEquals('African Network Information Center', $result->regyinfo['registrar']);
    }
}
