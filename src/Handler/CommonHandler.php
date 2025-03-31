<?php

namespace PHPWhois2\Handler;

use PHPWhois2\Data;

class CommonHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData);
        $data->regyinfo = $this->parseRegistryInfo($rawData);
        $data->rawData = $rawData;

        return $data;
    }
}
