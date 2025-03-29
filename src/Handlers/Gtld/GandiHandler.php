<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class GandiHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'owner-c',
            'admin' => 'admin-c',
            'tech' => 'tech-c',
            'billing' => 'bill-c',
        ];

        $trans = [
            'nic-hdl:' => 'handle',
            'person:' => 'name',
            'zipcode:' => 'address.pcode',
            'city:' => 'address.city',
            'lastupdated:' => 'changed',
            'owner-name:' => '',
        ];

        return static::easyParser($data_str, $items, 'dmy', $trans);
    }
}
