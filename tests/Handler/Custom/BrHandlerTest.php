<?php

namespace PHPWhois2\Tests\Handler\Custom;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Handler\Custom\BrHandler;
use PHPWhois2\Whois;
use PHPWhois2\WhoisClient;

final class BrHandlerTest extends TestCase
{
    public function test(): void
    {
        $whois = new Whois();
        $result = $whois->lookup('registro.br');

        $reflectionObj = new \ReflectionObject($whois);
        $reflectionProp = $reflectionObj->getProperty('whoisClient');
        /** @var WhoisClient $whoisClient */
        $whoisClient = $reflectionProp->getValue($whois);

        self::assertEquals(BrHandler::class, $whoisClient->queryParams->handlerClass);

        // \file_put_contents('/text.txt', $result->rawData);
        // \var_dump($result->regrinfo);
        self::assertEquals('Núcleo de Inf. e Coord. do Ponto BR - NIC.BR', $result->regrinfo['owner']['organization']);
        self::assertEquals('FAN', $result->regrinfo['owner']['handle']);
        self::assertEquals('registro.br', $result->regrinfo['domain']['name']);
        self::assertEquals('yes', $result->regrinfo['registered']);
    }
}
