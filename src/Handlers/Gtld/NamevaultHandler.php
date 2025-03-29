<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class NamevaultHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact:',
            'billing' => 'Billing Contact:',
            'domain.name' => 'Domain Name:',
            'domain.nserver.' => 'Name Servers',
            'domain.created' => 'Creation Date:',
            'domain.expires' => 'Expiration Date:',
            'domain.status' => 'Status:',
        ];

        return static::easyParser($data_str, $items, 'dmy', [], true, true);
    }
}
