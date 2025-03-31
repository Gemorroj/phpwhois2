<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class RwhoisHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'network:Organization-Name:' => 'owner.name',
            'network:Organization;I:' => 'owner.organization',
            'network:Organization-City:' => 'owner.address.city',
            'network:Organization-Zip:' => 'owner.address.pcode',
            'network:Organization-Country:' => 'owner.address.country',
            'network:IP-Network-Block:' => 'network.inetnum',
            'network:IP-Network:' => 'network.inetnum',
            'network:Network-Name:' => 'network.name',
            'network:ID:' => 'network.handle',
            'network:Created:' => 'network.created',
            'network:Updated:' => 'network.changed',
            'network:Tech-Contact;I:' => 'tech.email',
            'network:Admin-Contact;I:' => 'admin.email',
        ];

        $res = $this->generic_parser_b($rawData, $items, 'Ymd', false);

        if (isset($res['disclaimer'])) {
            unset($res['disclaimer']);
        }

        $data = new Data();
        $data->regrinfo = $res;
        $data->regyinfo = [];
        $data->rawData = $rawData;

        return $data;
    }
}
