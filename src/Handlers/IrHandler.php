<?php

namespace PHPWhois2\Handlers;

class IrHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $translate = [
            'nic-hdl' => 'handle',
            'org' => 'organization',
            'e-mail' => 'email',
            'person' => 'name',
            'fax-no' => 'fax',
            'domain' => 'name',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'holder-c' => 'owner',
        ];

        return [
            'regrinfo' => static::generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd'),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'http://whois.nic.ir/',
                'registrar' => 'NIC-IR',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
