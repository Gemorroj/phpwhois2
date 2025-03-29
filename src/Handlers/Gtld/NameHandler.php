<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class NameHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'REGISTRANT CONTACT INFO',
            'admin' => 'ADMINISTRATIVE CONTACT INFO',
            'tech' => 'TECHNICAL CONTACT INFO',
            'billing' => 'BILLING CONTACT INFO',
            'domain.name' => 'Domain Name:',
            'domain.sponsor' => 'Registrar',
            'domain.created' => 'Creation Date',
            'domain.expires' => 'Expiration Date',
        ];

        $extra = [
            'phone:' => 'phone',
            'email address:' => 'email',
        ];

        return static::easyParser($data_str, $items, 'y-m-d', $extra, false, true);
    }
}
