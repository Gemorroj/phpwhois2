<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class TmagnicHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Owner Contact:',
            'admin' => 'Admin Contact',
            'tech' => 'Technical Contact',
            'domain.name' => 'Domain Name:',
            'domain.nserver.' => 'Domain servers in listed order:',
            'domain.expires' => 'Record expires on: ',
            'domain.changed' => 'Record last updated on: ',
            '' => 'Zone Contact',
            '#' => 'Punycode Name:',
        ];

        return static::easyParser($data_str, $items, 'ymd', [], false, true);
    }
}
