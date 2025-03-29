<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class GodaddyHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
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

        $r = static::getBlocks($data_str, $items);
        $r['owner'] = static::getContact($r['owner']);
        $r['admin'] = static::getContact($r['admin'], [], true);
        $r['tech'] = static::getContact($r['tech'], [], true);

        return static::formatDates($r, 'dmy');
    }
}
