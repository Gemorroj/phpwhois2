<?php

namespace Tests;

class ip_handlerTest extends \PHPUnit\Framework\TestCase
{
    public function testParseIp(): void
    {
        $whois = new \phpWhois\Whois();
        $result = $whois->lookup('216.58.209.195');
        self::assertIsArray($result);
    }
}
