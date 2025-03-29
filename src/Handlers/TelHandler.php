<?php

namespace PHPWhois2\Handlers;

class TelHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        return [
            'regrinfo' => static::generic_parser_b($data_str['rawdata']),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.telnic.org',
                'registrar' => 'Telnic',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
