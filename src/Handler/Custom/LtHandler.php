<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class LtHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'contact nic-hdl:' => 'handle',
            'contact name:' => 'name',
        ];

        $items = [
            'admin' => 'Contact type:      Admin',
            'tech' => 'Contact type:      Tech',
            'zone' => 'Contact type:      Zone',
            'owner.name' => 'Registrar:',
            'owner.email' => 'Registrar email:',
            'domain.status' => 'Status:',
            'domain.created' => 'Registered:',
            'domain.changed' => 'Last updated:',
            'domain.nserver.' => 'NS:',
            '' => '%',
        ];

        $data = new Data();
        $data->regrinfo = $this->easyParser($rawData, $items, 'ymd', $translate);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'registrar' => 'DOMREG.LT',
            'referrer' => 'https://www.domreg.lt',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
