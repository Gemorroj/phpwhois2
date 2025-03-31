<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class PhHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'created:' => 'domain.created',
            'changed:' => 'domain.changed',
            'status:' => 'domain.status',
            'nserver:' => 'domain.nserver.',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($rawData);
        $data->rawData = $rawData;

        if (!isset($data->regrinfo['domain']['name'])) {
            $data->regrinfo['domain']['name'] = $query;
        }

        return $data;
    }
}
