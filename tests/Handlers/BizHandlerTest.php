<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\BizHandler;
use PHPWhois2\WhoisClient;

final class BizHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new BizHandler(new WhoisClient(), false);
    }

    public function testParseNeulevelDotBiz(): void
    {
        $query = 'neulevel.biz';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'neulevel.biz',
                'changed' => '2022-12-21',
                'created' => '2001-09-30',
                'expires' => '2023-11-06',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
