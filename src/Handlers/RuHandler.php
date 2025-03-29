<?php

namespace PHPWhois2\Handlers;

class RuHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'domain:' => 'domain.name',
            'registrar:' => 'domain.sponsor',
            'state:' => 'domain.status',
            'nserver:' => 'domain.nserver.',
            'source:' => 'domain.source',
            'created:' => 'domain.created',
            'paid-till:' => 'domain.expires',
            'type:' => 'owner.type',
            'org:' => 'owner.organization',
            'phone:' => 'owner.phone',
            'fax-no:' => 'owner.fax',
            'e-mail:' => 'owner.email',
        ];

        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], $items, 'dmy'),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'http://www.ripn.net',
                'registrar' => 'RU-CENTER-REG-RIPN',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        if (empty($r['regrinfo']['domain']['status'])) {
            $r['regrinfo']['registered'] = 'no';
        }

        return $r;
    }
}
