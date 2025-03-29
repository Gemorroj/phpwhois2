<?php

namespace PHPWhois2\Handlers;

class CaHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        return [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], [], 'ymd'),
            'regyinfo' => [
                'registrar' => 'CIRA',
                'referrer' => 'https://www.cira.ca/',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
