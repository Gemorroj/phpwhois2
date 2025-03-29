<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class AscioHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative ',
            'tech' => 'Technical ',
            'domain.name' => 'Domain name:',
            'domain.nserver.' => 'Domain servers in listed order:',
            'domain.created' => 'Record created:',
            'domain.expires' => 'Record expires:',
            'domain.changed' => 'Record last updated:',
        ];

        return static::easyParser($data_str, $items, 'ymd', [], false, true);
    }
}
