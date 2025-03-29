<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class NamejuiceHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant Contact:',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact:',
            'domain.name' => 'Domain name:',
            'domain.nserver.' => 'Name Servers:',
            'domain.created' => 'Creation date:',
            'domain.expires' => 'Expiration date:',
            'domain.changed' => 'Update date:',
            'domain.status' => 'Status:',
            'domain.sponsor' => 'Registration Service Provided By:',
        ];

        return static::easyParser($data_str, $items, 'dmy', [], true, true);
    }
}
