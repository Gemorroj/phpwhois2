<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class CzHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
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

        $data = new Data();
        $data->regrinfo = $this->generic_parser_a($rawData, $translate, $contacts);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.nic.cz',
            'registrar' => 'CZ-NIC',
        ] : [];
        $data->rawData = $rawData;

        if ('Your connection limit exceeded. Please slow down and try again later.' === $data->rawData[0]) {
            $data->regrinfo['registered'] = 'unknown';
        }

        return $data;
    }
}
