<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class IeHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;

        $reg = $this->generic_parser_b($rawData);

        if (isset($reg['domain']['descr'])) {
            $reg['owner']['organization'] = $reg['domain']['descr'][0];
            unset($reg['domain']['descr']);
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = [
            'referrer' => 'http://www.domainregistry.ie',
            'registrar' => 'IE Domain Registry',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
