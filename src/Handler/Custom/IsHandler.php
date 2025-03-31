<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class IsHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'person' => 'name',
        ];

        $contacts = [
            'owner-c' => 'owner',
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'billing-c' => 'billing',
            'zone-c' => 'zone',
        ];

        $reg = $this->generic_parser_a($rawData, $translate, $contacts, 'domain', 'mdy');

        if (isset($reg['domain']['descr'])) {
            $reg['owner']['name'] = \array_shift($reg['domain']['descr']);
            $reg['owner']['address'] = $reg['domain']['descr'];
            unset($reg['domain']['descr']);
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.isnic.is',
            'registrar' => 'ISNIC',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
