<?php

namespace PHPWhois2\Handlers\Ip;

use PHPWhois2\Handlers\AbstractHandler;

class LacnicHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
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

        $r = static::generic_parser_a($data_str, $translate, $contacts, 'network');

        unset($r['network']['owner'], $r['network']['ownerid'], $r['network']['responsible'], $r['network']['address'], $r['network']['phone'], $r['network']['aut-num'], $r['network']['nsstat'], $r['network']['nslastaa'], $r['network']['inetrev']);

        if (!empty($r['network']['aut-num'])) {
            $r['network']['handle'] = $r['network']['aut-num'];
        }

        if (isset($r['network']['nserver'])) {
            $r['network']['nserver'] = \array_unique($r['network']['nserver']);
        }

        $r = ['regrinfo' => $r];
        $r['regyinfo']['type'] = 'ip';
        $r['regyinfo']['registrar'] = 'Latin American and Caribbean IP address Regional Registry';

        return $r;
    }
}
