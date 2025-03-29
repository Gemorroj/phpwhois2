<?php

namespace PHPWhois2\Handlers;

class CzHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $translate = [
            'expire' => 'expires',
            'registered' => 'created',
            'nserver' => 'nserver',
            'domain' => 'name',
            'contact' => 'handle',
            'reg-c' => '',
            'descr' => 'desc',
            'e-mail' => 'email',
            'person' => 'name',
            'org' => 'organization',
            'fax-no' => 'fax',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'bill-c' => 'billing',
            'registrant' => 'owner',
        ];

        $r = [
            'regrinfo' => static::generic_parser_a($data_str['rawdata'], $translate, $contacts),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.nic.cz',
                'registrar' => 'CZ-NIC',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        if ('Your connection limit exceeded. Please slow down and try again later.' === $data_str['rawdata'][0]) {
            $r['regrinfo']['registered'] = 'unknown';
        }

        return $r;
    }
}
