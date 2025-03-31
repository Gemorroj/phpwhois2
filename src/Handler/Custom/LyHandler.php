<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class LyHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact:',
            'domain.name' => 'Domain Name:',
            'domain.status' => 'Domain Status:',
            'domain.created' => 'Created:',
            'domain.changed' => 'Updated:',
            'domain.expires' => 'Expired:',
            'domain.nserver' => 'Domain servers in listed order:',
        ];

        $extra = ['zip/postal code:' => 'address.pcode'];

        $reg = $this->getBlocks($rawData, $items);

        if (!empty($reg['domain']['name'])) {
            $reg = $this->getContacts($reg, $extra);
            $reg['domain']['name'] = $reg['domain']['name'][0];
            $reg['registered'] = 'yes';
        } else {
            $reg = [];
            $reg['registered'] = 'no';
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.nic.ly',
            'registrar' => 'Libya ccTLD',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
