<?php

namespace PHPWhois2\Handlers;

class AeroHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], [], 'ymd'),
            'regyinfo' => [
                'referrer' => 'http://www.nic.aero',
                'registrar' => 'Societe Internationale de Telecommunications Aeronautiques SC',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        return $r;
    }
}
