<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class HuHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain:' => 'domain.name',
            'record created:' => 'domain.created',
        ];

        $reg = $this->generic_parser_b($rawData, $items, 'ymd');

        if (isset($reg['domain'])) {
            $reg['registered'] = 'yes';
        } else {
            $reg['registered'] = 'no';
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = [
            'referrer' => 'http://www.nic.hu',
            'registrar' => 'HUNIC',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
