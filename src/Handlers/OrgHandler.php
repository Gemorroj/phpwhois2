<?php

namespace PHPWhois2\Handlers;

class OrgHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $r = [
            'regrinfo' => static::generic_parser_b($data_str['rawdata']),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://thenew.org/org-people/',
                'registrar' => 'Public Interest Registry',
            ],
            'rawdata' => $data_str['rawdata'] ?? null,
        ];

        if (!\strncmp($data_str['rawdata'][0], 'WHOIS LIMIT EXCEEDED', 20)) {
            $r['regrinfo']['registered'] = 'unknown';
        }

        return $r;
    }
}
