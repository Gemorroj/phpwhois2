<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class LuHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domainname:' => 'domain.name',
            'domaintype:' => 'domain.status',
            'nserver:' => 'domain.nserver.',
            'registered:' => 'domain.created',
            'source:' => 'domain.source',
            'ownertype:' => 'owner.type',
            'org-name:' => 'owner.organization',
            'org-address:' => 'owner.address.',
            'org-zipcode:' => 'owner.address.pcode',
            'org-city:' => 'owner.address.city',
            'org-country:' => 'owner.address.country',
            'adm-name:' => 'admin.name',
            'adm-address:' => 'admin.address.',
            'adm-zipcode:' => 'admin.address.pcode',
            'adm-city:' => 'admin.address.city',
            'adm-country:' => 'admin.address.country',
            'adm-email:' => 'admin.email',
            'tec-name:' => 'tech.name',
            'tec-address:' => 'tech.address.',
            'tec-zipcode:' => 'tech.address.pcode',
            'tec-city:' => 'tech.address.city',
            'tec-country:' => 'tech.address.country',
            'tec-email:' => 'tech.email',
            'bil-name:' => 'billing.name',
            'bil-address:' => 'billing.address.',
            'bil-zipcode:' => 'billing.address.pcode',
            'bil-city:' => 'billing.address.city',
            'bil-country:' => 'billing.address.country',
            'bil-email:' => 'billing.email',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items, 'dmy');
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.dns.lu',
            'registrar' => 'DNS-LU',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
