<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;
use PHPWhois2\Handler\Gtld\GtldHandler;

class IntHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $iana = new GtldHandler($this->whoisClient);

        $data = new Data();
        $data->regrinfo = $iana->parse($rawData, $query)->regrinfo;
        $data->regyinfo = [
            'referrer' => 'http://www.iana.org/int-dom/int.htm',
            'registrar' => 'Internet Assigned Numbers Authority',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
