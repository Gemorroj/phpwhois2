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
 * @copyright Copyright (c) 2020 Joshua Smith
 */

namespace phpWhois\Tests\Handlers;

use phpWhois\Handlers\EuHandler;
use phpWhois\WhoisClient;

final class EuHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new EuHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotEu(): void
    {
        $query = 'google.eu';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            // 'domain'     => [
            //     'name'    => 'google.eu',
            //     'changed' => '2020-01-13',
            //     'created' => '2003-03-17',
            //     'expires' => '2022-03-17',
            // ],
            'registered' => 'yes',
        ];

        // self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'],'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }

    public function testParseEuridDotEu(): void
    {
        $query = 'eurid.eu';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            // 'domain'     => [
            //     'name'    => 'eurid.eu',
            //     'changed' => '2020-08-03',
            //     'created' => '2003-03-10',
            //     'expires' => '2023-05-08',
            // ],
            'registered' => 'yes',
        ];

        // self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected['domain'], $actual['regrinfo']['domain'], $expected['domain'],'Whois data may have changed');
        self::assertEquals($expected['registered'], $actual['regrinfo']['registered'], 'Whois data may have changed');
        self::assertArrayHasKey('rawdata', $actual);
        self::assertArrayIsEqualToArrayOnlyConsideringListOfKeys($fixture, $actual['rawdata'], $fixture, 'Fixture data may be out of date');
    }
}
