<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class Names4everHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative Contact',
            'tech' => 'Technical  Contact',
            'domain.name' => 'Domain Name:',
            'domain.sponsor' => 'Registrar Name....:',
            'domain.referrer' => 'Registrar Homepage:',
            'domain.nserver' => 'DNS Servers:',
            'domain.created' => 'Record created on',
            'domain.expires' => 'Record expires on',
            'domain.changed' => 'Record last updated on',
            'domain.status' => 'Domain status:',
        ];

        return static::easyParser($data_str, $items, 'dmy', [], false, true);
    }
}
