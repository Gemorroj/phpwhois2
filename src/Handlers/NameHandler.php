<?php

namespace PHPWhois2\Handlers;

class NameHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        return [
            'regrinfo' => static::generic_parser_b($data_str['rawdata']),
            'regyinfo' => [
                'referrer' => 'https://www.nic.name/',
                'registrar' => 'Global Name Registry',
            ],
        ];
    }
}
