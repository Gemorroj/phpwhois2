<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class SiHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'nic-hdl' => 'handle',
            'nameserver' => 'nserver',
        ];

        $contacts = [
            'registrant' => 'owner',
            'tech-c' => 'tech',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_a($rawData, $translate, $contacts, 'domain', 'Ymd');
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.arnes.si',
            'registrar' => 'ARNES',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
