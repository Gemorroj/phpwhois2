<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class SeHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain:' => 'domain.name',
            'state:' => 'domain.status.',
            'status:' => 'domain.status.',
            'expires:' => 'domain.expires',
            'created:' => 'domain.created',
            'modified:' => 'domain.changed',
            'nserver:' => 'domain.nserver.',
            'registrar:' => 'domain.sponsor',
            'holder:' => 'owner.handle',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items, 'ymd', false);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.nic-se.se',
            'registrar' => 'NIC-SE',
        ] : [];
        $data->rawData = $rawData;

        $data->regrinfo['registered'] = isset($data->regrinfo['domain']['name']) ? 'yes' : 'no';

        return $data;
    }
}
