<?php

namespace PHPWhois2\Tests\Handlers\Gtld;

use PHPWhois2\Handlers\Gtld\AfternicHandler;
use PHPWhois2\Tests\Handlers\AbstractHandler;
use PHPWhois2\WhoisClient;

final class AfternicHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        self::markTestSkipped('Not sure what to do with this yet');
        parent::setUp();

        $this->handler = new AfternicHandler(new WhoisClient(), false);
    }

    public function testParseBuydomainsDotCom(): void
    {
        $query = 'buydomains.com';

        $fixture = $this->loadFixture($query);

        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'buydomains.com',
                // 'changed' => '2020-08-03',
                'created' => '2003-03-10',
                'expires' => '2023-05-08',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
