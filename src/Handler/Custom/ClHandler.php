<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class ClHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'admin' => '(Administrative Contact)',
            'tech' => 'Contacto Técnico (Technical Contact):',
            // 'domain.nserver' => 'Servidores de nombre (Domain servers):',
            'domain.nserver' => 'Name server:',
            'domain.changed' => '(Database last updated on):',
            'domain.created' => 'Creation date:',
            'domain.expires' => 'Expiration date:',
        ];

        $trans = [
            'organización:' => 'organization',
            'nombre      :' => 'name',
        ];

        $data = new Data();
        $data->regrinfo = $this->easyParser(\array_filter($rawData), $items, 'd-m-y', $trans);
        $data->regyinfo = [
            'referrer' => 'http://www.nic.cl',
            'registrar' => 'NIC Chile',
        ];
        $data->rawData = $rawData;

        if (!isset($data->regrinfo['domain']['name'])) {
            $data->regrinfo['domain']['name'] = $query;
        }

        return $data;
    }
}
