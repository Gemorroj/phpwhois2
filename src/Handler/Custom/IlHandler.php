<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class IlHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;

        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'person' => 'name',
            'personname' => 'name',
            'address' => 'address',
        ];

        $contacts = [
            'registrant' => 'owner',
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'billing-c' => 'billing',
            'zone-c' => 'zone',
        ];

        \array_splice($data_str['rawdata'], 16, 1);
        \array_splice($data_str['rawdata'], 18, 1);

        $reg = $this->generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

        if ($reg['disclaimer']) {
            unset($reg['disclaimer']);
        }

        if (isset($reg['domain']['remarks'])) {
            unset($reg['domain']['remarks']);
        }

        if (isset($reg['domain']['descr:'])) {
            foreach ($reg['domain']['descr:'] as $key => $val) {
                $v = \trim(\substr(\strstr($val, ':'), 1));
                if (\str_contains($val, '[organization]:')) {
                    $reg['owner']['organization'] = $v;
                    continue;
                }
                if (\str_contains($val, '[phone]:')) {
                    $reg['owner']['phone'] = $v;
                    continue;
                }
                if (\str_contains($val, '[fax-no]:')) {
                    $reg['owner']['fax'] = $v;
                    continue;
                }
                if (\str_contains($val, '[e-mail]:')) {
                    $reg['owner']['email'] = $v;
                    continue;
                }

                $reg['owner']['address'][$key] = $v;
            }

            unset($reg['domain']['descr:']);
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = [
            'referrer' => 'https://www.isoc.org.il/',
            'registrar' => 'ISOC-IL',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
