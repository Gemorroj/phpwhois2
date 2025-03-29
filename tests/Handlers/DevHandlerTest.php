<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\DevHandler;
use PHPWhois2\WhoisClient;

final class DevHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new DevHandler(new WhoisClient(), false);
    }

    public function testParseOstapDotDev(): void
    {
        $query = 'ostap.dev';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('ostap.dev', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2025-03-02', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('2019-02-28', $actual['regrinfo']['domain']['created']);
        self::assertEquals('2026-02-28', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
