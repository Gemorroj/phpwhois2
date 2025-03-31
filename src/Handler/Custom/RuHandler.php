<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class RuHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain:' => 'domain.name',
            'registrar:' => 'domain.sponsor',
            'state:' => 'domain.status',
            'nserver:' => 'domain.nserver.',
            'source:' => 'domain.source',
            'created:' => 'domain.created',
            'paid-till:' => 'domain.expires',
            'type:' => 'owner.type',
            'org:' => 'owner.organization',
            'phone:' => 'owner.phone',
            'fax-no:' => 'owner.fax',
            'e-mail:' => 'owner.email',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items, 'dmy');
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'http://www.ripn.net',
            'registrar' => 'RU-CENTER-REG-RIPN',
        ] : [];

        if (!isset($data->regrinfo['domain']['status'])) {
            $data->regrinfo['registered'] = 'no';
        } else {
            $data->regrinfo['registered'] = 'yes';
        }

        $data->rawData = $rawData;

        return $data;
    }
}
