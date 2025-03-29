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

use phpWhois\Handlers\CaHandler;
use phpWhois\WhoisClient;

final class CaHandlerTest extends AbstractHandler
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CaHandler(new WhoisClient(), false);
    }

    public function testParseGoogleDotCa(): void
    {
        $query = 'google.ca';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('google.ca', $actual['regrinfo']['domain']['name']);
        self::assertEquals('2020-04-28', $actual['regrinfo']['domain']['changed']);
        self::assertEquals('2000-10-04', $actual['regrinfo']['domain']['created']);
        self::assertEquals('2021-04-28', $actual['regrinfo']['domain']['expires']);
        self::assertEquals('yes', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }

    public function testParseCiraDotCa(): void
    {
        $query = 'cira.ca';

        $fixture = $this->loadFixture($query);
        $data = [
            'rawdata' => $fixture,
            'regyinfo' => [],
        ];

        $actual = $this->handler->parse($data, $query);

        self::assertEquals('CIRA', $actual['regyinfo']['registrar']);
        self::assertEquals('https://www.cira.ca/', $actual['regyinfo']['referrer']);
        self::assertEquals('no', $actual['regrinfo']['registered']);

        self::assertArrayHasKey('rawdata', $actual);
        self::assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
