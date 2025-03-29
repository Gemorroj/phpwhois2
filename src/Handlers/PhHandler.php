<?php

namespace PHPWhois2\Handlers;

class PhHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'created:' => 'domain.created',
            'changed:' => 'domain.changed',
            'status:' => 'domain.status',
            'nserver:' => 'domain.nserver.',
        ];

        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], $items),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']),
            'rawdata' => $data_str['rawdata'],
        ];

        if (!isset($r['regrinfo']['domain']['name'])) {
            $r['regrinfo']['domain']['name'] = $query;
        }

        return $r;
    }
}
