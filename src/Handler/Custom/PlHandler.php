<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class PlHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.created' => 'created:',
            'domain.changed' => 'last modified:',
            'domain.sponsor' => 'REGISTRAR:',
            '#' => 'WHOIS displays data with a delay not exceeding 15 minutes in relation to the .pl Registry system',
        ];

        $data = new Data();
        $data->regrinfo = $this->easyParser($rawData, $items, 'ymd');
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.dns.pl/english/index.html',
            'registrar' => 'NASK',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
