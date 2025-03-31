<?php

namespace PHPWhois2\Handler\Ip;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class LacnicHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl-br' => 'handle',
            'nic-hdl' => 'handle',
            'person' => 'name',
            'netname' => 'name',
            'descr' => 'desc',
            'country' => 'address.country',
        ];

        $contacts = [
            'owner-c' => 'owner',
            'tech-c' => 'tech',
            'abuse-c' => 'abuse',
            'admin-c' => 'admin',
        ];

        $r = $this->generic_parser_a($rawData, $translate, $contacts, 'network');

        unset($r['network']['owner'], $r['network']['ownerid'], $r['network']['responsible'], $r['network']['address'], $r['network']['phone'], $r['network']['aut-num'], $r['network']['nsstat'], $r['network']['nslastaa'], $r['network']['inetrev']);

        if (!empty($r['network']['aut-num'])) {
            $r['network']['handle'] = $r['network']['aut-num'];
        }

        if (isset($r['network']['nserver'])) {
            $r['network']['nserver'] = \array_unique($r['network']['nserver']);
        }

        $data = new Data();
        $data->regrinfo = $r;
        $data->regyinfo = [
            'type' => 'ip',
            'registrar' => 'Latin American and Caribbean IP address Regional Registry',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
