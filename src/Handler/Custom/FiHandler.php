<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class FiHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.............:' => 'domain.name',
            'created............:' => 'domain.created',
            'modified...........:' => 'domain.changed',
            'expires............:' => 'domain.expires',
            'status.............:' => 'domain.status',
            'nserver............:' => 'domain.nserver.',
            'name...............:' => 'owner.name.',
            'address............:' => 'owner.address.',
            'country............:' => 'owner.country',
            'phone..............:' => 'owner.phone',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://domain.ficora.fi/',
            'registrar' => 'Finnish Communications Regulatory Authority',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
