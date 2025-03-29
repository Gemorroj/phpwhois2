<?php

namespace PHPWhois2\Handlers;

class BizHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], [], 'mdy'),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.neulevel.biz',
                'registrar' => 'NEULEVEL',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        return $r;
    }
}
