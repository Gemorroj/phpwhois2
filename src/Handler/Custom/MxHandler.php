<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class MxHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact:',
            'billing' => 'Billing Contact:',
            'domain.nserver' => 'Name Servers:',
            'domain.created' => 'Created On:',
            'domain.expires' => 'Expiration Date:',
            'domain.changed' => 'Last Updated On:',
            'domain.sponsor' => 'Registrar:',
        ];

        $extra = [
            'city:' => 'address.city',
            'state:' => 'address.state',
            'dns:' => '0',
        ];

        $reg = $this->easyParser($rawData, $items, 'dmy', $extra);
        if (empty($reg['domain']['created'])) {
            $reg['registered'] = 'no';
        } else {
            $reg['registered'] = 'yes';
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'registrar' => 'NIC Mexico',
            'referrer' => 'https://www.nic.mx/',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
