<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class NiclineHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative contact:',
            'tech' => 'Technical contact:',
            'domain.name' => 'Domain name:',
            'domain.nserver.' => 'Domain servers in listed order:',
            'domain.created' => 'Created:',
            'domain.expires' => 'Expires:',
            'domain.changed' => 'Last updated:',
        ];

        return static::easyParser($data_str, $items, 'dmy');
    }
}
