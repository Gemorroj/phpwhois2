<?php

namespace PHPWhois2\Handlers;

class UsHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        return [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], [], 'ymd'),
            'regyinfo' => [
                'referrer' => 'https://www.neustar.us',
                'registrar' => 'NEUSTAR INC.',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
