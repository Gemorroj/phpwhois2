<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class MonikerHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant',
            'admin' => 'Administrative ',
            'tech' => 'Technical ',
            'billing' => 'Billing ',
            'domain.name' => 'Domain Name:',
            'domain.nserver.' => 'Domain servers in listed order:',
            'domain.created' => 'Record created on: ',
            'domain.expires' => 'Domain Expires on: ',
            'domain.changed' => 'Database last updated on: ',
        ];

        return static::easyParser($data_str, $items, 'ymd');
    }
}
