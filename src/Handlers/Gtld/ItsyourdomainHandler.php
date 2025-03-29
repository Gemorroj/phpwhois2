<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class ItsyourdomainHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant',
            'admin' => 'Administrative',
            'tech' => 'Technical',
            'billing' => 'Billing',
            'domain.name' => 'Domain:',
            'domain.nserver.' => 'Domain Name Servers:',
            'domain.created' => 'Record created on ',
            'domain.expires' => 'Record expires on ',
            'domain.changed' => 'Record last updated on ',
        ];

        return static::easyParser($data_str, $items, 'mdy');
    }
}
