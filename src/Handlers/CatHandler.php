<?php

namespace PHPWhois2\Handlers;

class CatHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata']),
            'regyinfo' => [
                'registrar' => 'Domini punt CAT',
                'referrer' => 'https://domini.cat/',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        if (!isset($r['regrinfo']['domain']['name'])) {
            $r['regrinfo']['registered'] = 'no';
        }

        return $r;
    }
}
