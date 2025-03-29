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

/**
 * CaHandlerTest.
 */
class CaHandlerTest extends AbstractHandler
{
    /**
     * @var CaHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CaHandler(new WhoisClient());
        $this->handler->deepWhois = false;
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

        $this->assertEquals('google.ca', $actual['regrinfo']['domain']['name']);
        $this->assertEquals('2020-04-28', $actual['regrinfo']['domain']['changed']);
        $this->assertEquals('2000-10-04', $actual['regrinfo']['domain']['created']);
        $this->assertEquals('2021-04-28', $actual['regrinfo']['domain']['expires']);
        $this->assertEquals('yes', $actual['regrinfo']['registered']);

        $this->assertArrayHasKey('rawdata', $actual);
        $this->assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
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

        $this->assertEquals('CIRA', $actual['regyinfo']['registrar']);
        $this->assertEquals('https://www.cira.ca/', $actual['regyinfo']['referrer']);
        $this->assertEquals('no', $actual['regrinfo']['registered']);

        $this->assertArrayHasKey('rawdata', $actual);
        $this->assertEquals($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
