<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class DeHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.name' => 'Domain:',
            'domain.nserver.' => 'Nserver:',
            'domain.nserver.#' => 'Nsentry:',
            'domain.status' => 'Status:',
            'domain.changed' => 'Changed:',
            'domain.desc.' => 'Descr:',
            'owner' => '[Holder]',
            'admin' => '[Admin-C]',
            'tech' => '[Tech-C]',
            'zone' => '[Zone-C]',
        ];

        $extra = [
            'city:' => 'address.city',
            'postalcode:' => 'address.pcode',
            'countrycode:' => 'address.country',
            'remarks:' => '',
            'sip:' => 'sip',
            'type:' => '',
        ];

        $rawDataFiltered = \array_filter($rawData);

        $data = new Data();
        $data->regrinfo = $this->easyParser($rawDataFiltered, $items, 'ymd', $extra);
        $data->regyinfo = $this->parseRegistryInfo($rawDataFiltered) ? [
            'registrar' => 'DENIC eG',
            'referrer' => 'https://www.denic.de/',
        ] : [];
        $data->rawData = $rawData;

        if (!isset($data->regrinfo['domain']['status']) || 'free' === $data->regrinfo['domain']['status']) {
            $data->regrinfo['registered'] = 'no';
        } else {
            if (isset($data->regrinfo['domain']['changed'])) {
                $data->regrinfo['domain']['changed'] = \substr($data->regrinfo['domain']['changed'], 0, 10);
            }
            $data->regrinfo['registered'] = 'yes';
        }

        return $data;
    }
}
