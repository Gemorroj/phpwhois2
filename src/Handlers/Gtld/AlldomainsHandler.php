<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class AlldomainsHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative',
            'tech' => 'Technical',
            'domain.name' => 'Domain name:',
            'domain.sponsor' => 'Registrar:',
            'domain.nserver.' => 'Domain servers in listed order:',
        ];

        return static::easyParser($data_str, $items, 'ymd');
    }
}
