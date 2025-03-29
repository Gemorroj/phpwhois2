<?php

namespace PHPWhois2\Handlers;

class AsiaHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata']),
            'regyinfo' => [
                'referrer' => 'http://www.dotasia.org/',
                'registrar' => 'DotAsia',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        return $r;
    }
}
