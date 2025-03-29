<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\TodayHandler;
use PHPWhois2\WhoisClient;

final class TodayHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new TodayHandler(new WhoisClient(), false);
    }

    public function testParseModxDotToday(): void
    {
        $query = 'modx.today';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('modx.today', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2024-06-23', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('2014-05-09', $actual['regrinfo']['domain']['created']);
        self::assertEquals('2025-05-09', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
