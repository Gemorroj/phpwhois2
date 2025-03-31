<?php

namespace PHPWhois2\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPWhois2\WhoisClient;

final class WhoisClientTest extends TestCase
{
    #[DataProvider('serversProvider')]
    public function testParseServer($server, $result): void
    {
        $whoisClient = new WhoisClient();
        $reflectionObj = new \ReflectionObject($whoisClient);
        $reflectionMethod = $reflectionObj->getMethod('parseServer');
        $actual = $reflectionMethod->invoke($whoisClient, $server);
        self::assertEquals($result, $actual);
    }

    public static function serversProvider(): array
    {
        return [
            ['http://www.phpwhois.pw:80/', ['scheme' => 'http', 'host' => 'www.phpwhois.pw', 'port' => 80]],
            ['http://www.phpwhois.pw:80', ['scheme' => 'http', 'host' => 'www.phpwhois.pw', 'port' => 80]],
            ['http://www.phpwhois.pw', ['scheme' => 'http', 'host' => 'www.phpwhois.pw']],
            ['www.phpwhois.pw:80', ['host' => 'www.phpwhois.pw', 'port' => 80]],
            ['www.phpwhois.pw:80/', ['host' => 'www.phpwhois.pw', 'port' => 80]],
            ['www.phpwhois.pw', ['host' => 'www.phpwhois.pw', 'port' => null]],
            ['www.phpwhois.pw/', ['host' => 'www.phpwhois.pw', 'port' => null]],
            ['http://127.0.0.1:80/', ['scheme' => 'http', 'host' => '127.0.0.1', 'port' => 80]],
            ['http://127.0.0.1:80', ['scheme' => 'http', 'host' => '127.0.0.1', 'port' => 80]],
            ['http://127.0.0.1', ['scheme' => 'http', 'host' => '127.0.0.1']],
            ['127.0.0.1:80', ['host' => '127.0.0.1', 'port' => 80]],
            ['127.0.0.1:80/', ['host' => '127.0.0.1', 'port' => 80]],
            ['127.0.0.1', ['host' => '127.0.0.1', 'port' => null]],
            ['127.0.0.1/', ['host' => '127.0.0.1', 'port' => null]],
            ['http://[1a80:1f45::ebb:12]:80/', ['scheme' => 'http', 'host' => '[1a80:1f45::ebb:12]', 'port' => 80]],
            ['http://[1a80:1f45::ebb:12]:80', ['scheme' => 'http', 'host' => '[1a80:1f45::ebb:12]', 'port' => 80]],
            ['http://[1a80:1f45::ebb:12]', ['scheme' => 'http', 'host' => '[1a80:1f45::ebb:12]']],
            // ['http://1a80:1f45::ebb:12', ['scheme' => 'http', 'host' => '[1a80:1f45::ebb:12]']],
            ['[1a80:1f45::ebb:12]:80', ['host' => '[1a80:1f45::ebb:12]', 'port' => 80]],
            ['[1a80:1f45::ebb:12]:80/', ['host' => '[1a80:1f45::ebb:12]', 'port' => 80]],
            ['1a80:1f45::ebb:12', ['host' => '[1a80:1f45::ebb:12]', 'port' => null]],
            ['1a80:1f45::ebb:12/', ['host' => '[1a80:1f45::ebb:12]', 'port' => null]],
        ];
    }
}
