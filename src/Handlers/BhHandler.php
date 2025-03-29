<?php

namespace PHPWhois2\Handlers;

class BhHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata']),
            'rawdata' => $data_str['rawdata'],
        ];

        if (empty($r['regrinfo']['domain']['created'])) {
            $r['regrinfo']['registered'] = 'no';
        } else {
            $r['regrinfo']['registered'] = 'yes';
        }

        $r['regyinfo'] = $this->parseRegistryInfo($data_str['rawdata']) ?? [
            'referrer' => 'http://www.nic.bh/',
            'registrar' => 'NIC-BH',
        ];

        return $r;
    }
}
