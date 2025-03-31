<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class OrgzaHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.status' => 'Status:',
            'domain.nserver' => 'Domain name servers in listed order:',
            'domain.changed' => 'Record last updated on',
            'owner' => 'rwhois search on',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact:',
            'billing' => 'Billing Contact:',
            '#' => 'Search Again',
        ];

        $data = new Data();
        $data->regrinfo = $this->getBlocks($rawData, $items);
        $data->regyinfo = [
            'referrer' => 'http://www.org.za',
            'registrar' => 'The ORG.ZA Domain',
        ];
        $data->rawData = $rawData;

        if (isset($data->regrinfo['domain']['status'])) {
            $data->regrinfo['registered'] = 'yes';
            $data->regrinfo['domain']['handler'] = \strtok(\array_shift($data->regrinfo['owner']), ' ');
            $data->regrinfo = $this->getContacts($data->regrinfo);
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        return $data;
    }
}
