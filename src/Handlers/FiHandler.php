<?php

namespace PHPWhois2\Handlers;

class FiHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'domain.............:' => 'domain.name',
            'created............:' => 'domain.created',
            'modified...........:' => 'domain.changed',
            'expires............:' => 'domain.expires',
            'status.............:' => 'domain.status',
            'nserver............:' => 'domain.nserver.',
            'name...............:' => 'owner.name.',
            'address............:' => 'owner.address.',
            'country............:' => 'owner.country',
            'phone..............:' => 'owner.phone',
        ];

        return [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], $items, 'dmy'),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://domain.ficora.fi/',
                'registrar' => 'Finnish Communications Regulatory Authority',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
