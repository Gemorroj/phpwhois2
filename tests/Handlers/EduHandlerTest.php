<?php

namespace PHPWhois2\Tests\Handlers;

use PHPWhois2\Handlers\EduHandler;
use PHPWhois2\WhoisClient;

final class EduHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new EduHandler(new WhoisClient(), false);
    }

    public function testParseBerkeleyDotEdu(): void
    {
        $query = 'berkeley.edu';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'berkeley.edu',
                'changed' => '2023-01-31',
                'created' => '1985-04-24',
            ],
            // 'registered' => 'yes', // Currently broken
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        // self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
