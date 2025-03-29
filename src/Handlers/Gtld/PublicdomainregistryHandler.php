<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class PublicdomainregistryHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'owner#' => '(Registrant):',
            'admin' => 'Administrative Contact',
            'tech' => 'Technical Contact',
            'billing' => 'Billing Contact',
            'domain.name' => 'Domain name:',
            'domain.sponsor' => 'Registration Service Provided By:',
            'domain.nserver' => 'Domain servers in listed order:',
            'domain.changed' => 'Record last updated ',
            'domain.created' => 'Record created on',
            'domain.created#' => 'Creation Date:',
            'domain.expires' => 'Record expires on',
            'domain.expires#' => 'Expiration Date:',
            'domain.status' => 'Status:',
        ];

        return static::easyParser($data_str, $items, 'mdy', [], true, true);
    }
}
