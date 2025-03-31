<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class IrHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
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

        $data = new Data();
        $data->regrinfo = $this->generic_parser_a($rawData, $translate, $contacts, 'domain', 'Ymd');
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'http://whois.nic.ir/',
            'registrar' => 'NIC-IR',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
