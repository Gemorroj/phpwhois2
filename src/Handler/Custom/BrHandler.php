<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class BrHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl-br' => 'handle',
            'person' => 'name',
            'netname' => 'name',
            'domain' => 'name',
            'updated' => '',
        ];

        $contacts = [
            'owner-c' => 'owner',
            'tech-c' => 'tech',
            'admin-c' => 'admin',
            'billing-c' => 'billing',
        ];

        $reg = $this->generic_parser_a($rawData, $translate, $contacts, 'domain', 'Ymd');

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'registrar' => 'BR-NIC',
            'referrer' => 'https://www.nic.br',
        ] : [];
        $data->rawData = $rawData;

        if (\in_array('Permission denied.', $reg['disclaimer'], true)) {
            $data->regrinfo['registered'] = 'unknown';
        }

        if (isset($data->regrinfo['domain']['nsstat'])) {
            unset($data->regrinfo['domain']['nsstat']);
        }
        if (isset($data->regrinfo['domain']['nslastaa'])) {
            unset($data->regrinfo['domain']['nslastaa']);
        }
        if (isset($data->regrinfo['domain']['owner'])) {
            $data->regrinfo['owner']['organization'] = $data->regrinfo['domain']['owner'];
            unset($data->regrinfo['domain']['owner']);
        }
        if (isset($data->regrinfo['domain']['responsible'])) {
            unset($data->regrinfo['domain']['responsible']);
        }
        if (isset($data->regrinfo['domain']['address'])) {
            unset($data->regrinfo['domain']['address']);
        }
        if (isset($data->regrinfo['domain']['phone'])) {
            unset($data->regrinfo['domain']['phone']);
        }

        return $data;
    }
}
