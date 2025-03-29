<?php

/**
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @license
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 * @copyright Copyright (c) 2018 Joshua Smith
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\AeHandler;
use phpWhois\WhoisClient;

/**
 * AeHandlerTest.
 */
class AeHandlerTest extends AbstractHandler
{
    /**
     * @var AeHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new AeHandler(new WhoisClient());
        $this->handler->deepWhois = false;
    }

    public function testParseGoogleDotAe(): void
    {
        $query = 'google.ae';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'google.ae',
                'sponsor' => 'MarkMonitor',
                'status' => 'clientUpdateProhibited',
            ],
            'owner' => [
                'name' => 'Domain Administrator',
            ],
            'tech' => [
                'name' => 'Domain Administrator',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['owner'], $actual['regrinfo']['owner'], $expected['owner'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['tech'], $actual['regrinfo']['tech'], $expected['tech'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseNicDotAe(): void
    {
        $query = 'nic.ae';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'nic.ae',
                'sponsor' => 'Etisalat',
                'status' => 'clientUpdateProhibited',
            ],
            'owner' => [
                'name' => 'Emirates Telecommunications Corporation - Etisalat',
            ],
            'tech' => [
                'name' => 'Emirates Telecommunications Corporation - Etisalat',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['owner'], $actual['regrinfo']['owner'], $expected['owner'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['tech'], $actual['regrinfo']['tech'], $expected['tech'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseAedaDotAe(): void
    {
        $query = 'aeda.ae';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain' => [
                'name' => 'aeda.ae',
                'sponsor' => 'aeDA Regulator',
                'status' => 'ok',
            ],
            'owner' => [
                'name' => 'Telecommunication Regulatory Authority',
            ],
            'tech' => [
                'name' => '.ae Domain Administration',
            ],
            'registered' => 'yes',
        ];

        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['owner'], $actual['regrinfo']['owner'], $expected['owner'], 'Whois data may have changed');
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['tech'], $actual['regrinfo']['tech'], $expected['tech'], 'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
