<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\FjHandler;
use PHPWhois2\WhoisClient;

final class FjHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        self::markTestSkipped('.fj domain parsing broken');

        parent::setUp();

        $this->handler = new FjHandler(new WhoisClient(), false);
    }

    public function testParseFijiDotGovDotFj(): void
    {
        $query = 'fiji.gov.fj';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'fiji.gov.fj',
                // 'changed' => '2020-08-03',
                // 'created' => '2003-03-10',
                'expires' => '2020-12-31',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
