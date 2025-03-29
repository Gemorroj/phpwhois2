<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class MelbourneitHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'Domain Name..........' => 'domain.name',
            'Registration Date....' => 'domain.created',
            'Expiry Date..........' => 'domain.expires',
            'Organisation Name....' => 'owner.name',
            'Organisation Address.' => 'owner.address.',
            'Admin Name...........' => 'admin.name',
            'Admin Address........' => 'admin.address.',
            'Admin Email..........' => 'admin.email',
            'Admin Phone..........' => 'admin.phone',
            'Admin Fax............' => 'admin.fax',
            'Tech Name............' => 'tech.name',
            'Tech Address.........' => 'tech.address.',
            'Tech Email...........' => 'tech.email',
            'Tech Phone...........' => 'tech.phone',
            'Tech Fax.............' => 'tech.fax',
            'Name Server..........' => 'domain.nserver.',
        ];

        return static::generic_parser_b($data_str, $items, 'ymd');
    }
}
