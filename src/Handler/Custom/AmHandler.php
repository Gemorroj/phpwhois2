<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class AmHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'owner' => 'Registrant:',
            'domain.name' => 'Domain name:',
            'domain.created' => 'Registered:',
            'domain.changed' => 'Last modified:',
            'domain.nserver' => 'DNS servers:',
            'domain.status' => 'Status:',
            'tech' => 'Technical contact:',
            'admin' => 'Administrative contact:',
        ];

        $data = new Data();
        $data->regrinfo = $this->getBlocks(\array_filter($rawData), $items);
        $data->rawData = $rawData;

        if (!empty($data->regrinfo['domain']['name'])) {
            $data->regyinfo = [
                'referrer' => 'http://www.isoc.am',
                'registrar' => 'ISOCAM',
            ];
            $data->regrinfo = $this->getContacts($data->regrinfo);
            $data->regrinfo['registered'] = 'yes';
        } else {
            $data->regyinfo = [];
            $data->regrinfo['registered'] = 'no';
        }

        return $data;
    }
}
