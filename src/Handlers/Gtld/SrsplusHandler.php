<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class SrsplusHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative',
            'tech' => 'Technical',
            'billing' => 'Billing',
            'domain.name' => 'Domain Name:',
            'domain.nserver' => 'Domain servers:',
            'domain.created' => 'Record created on',
            'domain.expires' => 'Record expires on',
        ];

        return static::easyParser($data_str, $items, 'ymd', [], true, true);
    }
}
