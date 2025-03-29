<?php

namespace PHPWhois2\Handlers;

class NoHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'Domain Name................:' => 'domain.name',
            'Created:' => 'domain.created',
            'Last updated:' => 'domain.changed',
            'NORID Handle...............:' => 'domain.handle',
        ];

        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], $items, 'ymd', false),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.norid.no/en/',
                'registrar' => 'Norid',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        $r['regrinfo']['registered'] = isset($r['regrinfo']['domain']['name']) ? 'yes' : 'no';

        return $r;
    }
}
