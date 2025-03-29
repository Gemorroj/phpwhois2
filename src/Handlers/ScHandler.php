<?php

namespace PHPWhois2\Handlers;

class ScHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        return [
            'regrinfo' => static::generic_parser_b($data_str['rawdata'], [], 'dmy'),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.nic.sc',
                'registrar' => 'VCS (Pty) Limited',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
