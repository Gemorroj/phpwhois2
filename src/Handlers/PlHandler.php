<?php

namespace PHPWhois2\Handlers;

class PlHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'domain.created' => 'created:',
            'domain.changed' => 'last modified:',
            'domain.sponsor' => 'REGISTRAR:',
            '#' => 'WHOIS displays data with a delay not exceeding 15 minutes in relation to the .pl Registry system',
        ];

        return [
            'regrinfo' => static::easyParser($data_str['rawdata'], $items, 'ymd'),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.dns.pl/english/index.html',
                'registrar' => 'NASK',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
