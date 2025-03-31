<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class NoHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'Domain Name................:' => 'domain.name',
            'Created:' => 'domain.created',
            'Last updated:' => 'domain.changed',
            'NORID Handle...............:' => 'domain.handle',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items, 'ymd', false);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.norid.no/en/',
            'registrar' => 'Norid',
        ] : [];
        $data->rawData = $rawData;

        $data->regrinfo['registered'] = isset($data->regrinfo['domain']['name']) ? 'yes' : 'no';

        return $data;
    }
}
