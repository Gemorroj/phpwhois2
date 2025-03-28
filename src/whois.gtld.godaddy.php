<?php

/**
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
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
 *
 * @see http://phpwhois.pw
 *
 * @copyright Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @copyright Maintained by David Saez
 * @copyright Copyright (c) 2014 Dmitry Lukashin
 */
if (!\defined('__GODADDY_HANDLER__')) {
    \define('__GODADDY_HANDLER__', 1);
}

class godaddy_handler
{
    // FIXME. This is a temporary fix :-(
    public $deepWhois = false;

    public function parse($data_str, $query)
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative Contact',
            'tech' => 'Technical Contact',
            'domain.name' => 'Domain Name:',
            'domain.nserver.' => 'Domain servers in listed order:',
            'domain.created' => 'Created on:',
            'domain.expires' => 'Expires on:',
            'domain.changed' => 'Last Updated on:',
            'domain.sponsor' => 'Registered through:',
        ];

        $r = phpWhois\Handlers\AbstractHandler::getBlocks($data_str, $items);
        $r['owner'] = phpWhois\Handlers\AbstractHandler::getContact($r['owner']);
        $r['admin'] = phpWhois\Handlers\AbstractHandler::getContact($r['admin'], [], true);
        $r['tech'] = phpWhois\Handlers\AbstractHandler::getContact($r['tech'], [], true);

        return phpWhois\Handlers\AbstractHandler::formatDates($r, 'dmy');
    }
}
