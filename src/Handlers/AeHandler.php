<?php

namespace PHPWhois2\Handlers;

class AeHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'Domain Name:' => 'domain.name',
            'Registrar Name:' => 'domain.sponsor',
            'Status:' => 'domain.status',
            'Registrant Contact ID:' => 'owner.handle',
            'Registrant Contact Name:' => 'owner.name',
            'Registrant Contact Organisation:' => 'owner.organization',
            'Tech Contact Name:' => 'tech.name',
            'Tech Contact ID:' => 'tech.handle',
            'Tech Contact Organisation:' => 'tech.organization',
            'Name Server:' => 'domain.nserver.',
        ];

        return [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], $items, 'ymd'),
            'regyinfo' => [
                'referrer' => 'http://www.nic.ae',
                'registrar' => 'UAENIC',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
