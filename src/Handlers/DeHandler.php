<?php

namespace PHPWhois2\Handlers;

class DeHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
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

        $rawData = $this->removeBlankLines($data_str['rawdata']);

        $r = [
            'rawdata' => $data_str['rawdata'],
            'regrinfo' => static::easyParser($rawData, $items, 'ymd', $extra),
            'regyinfo' => $this->parseRegistryInfo($rawData) ?? [
                'registrar' => 'DENIC eG',
                'referrer' => 'https://www.denic.de/',
            ],
        ];

        if (!isset($r['regrinfo']['domain']['status']) || 'free' === $r['regrinfo']['domain']['status']) {
            $r['regrinfo']['registered'] = 'no';
        } else {
            if (isset($r['regrinfo']['domain']['changed'])) {
                $r['regrinfo']['domain']['changed'] = \substr($r['regrinfo']['domain']['changed'], 0, 10);
            }
            $r['regrinfo']['registered'] = 'yes';
        }

        return $r;
    }
}
