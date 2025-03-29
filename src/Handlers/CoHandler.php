<?php

namespace PHPWhois2\Handlers;

class CoHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [
            'rawdata' => $data_str['rawdata'],
        ];

        $r['regrinfo'] = static::generic_parser_b($data_str['rawdata'], [], 'mdy');
        $r['regyinfo']['referrer'] = 'http://www.cointernet.com.co/';
        $r['regyinfo']['registrar'] = '.CO Internet, S.A.S.';

        return $r;
    }
}
