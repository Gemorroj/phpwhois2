<?php

namespace PHPWhois2\Handlers;

class InfoHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [];
        $r['regrinfo'] = static::generic_parser_b($data_str['rawdata']);
        $r['regyinfo'] = [
            'referrer' => 'https://whois.afilias.info',
            'registrar' => 'Afilias Global Registry Services',
        ];

        return $r;
    }
}
