<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class OvhHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact:',
            'billing' => 'Billing Contact:',
            'domain.sponsor' => 'Registrar of Record:',
            'domain.changed' => 'Record last updated on',
            'domain.expires' => 'Record expires on',
            'domain.created' => 'Record created on',
        ];

        return static::easyParser($data_str, $items, 'mdy', [], false, true);
    }
}
