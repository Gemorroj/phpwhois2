<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class ChHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'owner' => 'Holder of domain name:',
            'domain.name' => 'Domain name:',
            'domain.created' => 'Date of last registration:',
            'domain.changed' => 'Date of last modification:',
            'tech' => 'Technical contact:',
            'domain.nserver' => 'Name servers:',
            'domain.dnssec' => 'DNSSEC:',
        ];

        $trans = [
            'contractual language:' => 'language',
        ];

        $data = new Data();
        $data->regrinfo = $this->getBlocks($rawData, $items);
        $data->regyinfo = [
            'referrer' => 'https://www.nic.ch/',
            'registrar' => 'SWITCH Domain Name Registration',
        ];
        $data->rawData = $rawData;

        if ($data->regrinfo['domain']['name']) {
            $data->regrinfo = $this->getContacts($data->regrinfo, $trans);

            $data->regrinfo['domain']['name'] = $data->regrinfo['domain']['name'][0];

            if (isset($data->regrinfo['domain']['changed'][0])) {
                $data->regrinfo['domain']['changed'] = $this->getDate($data->regrinfo['domain']['changed'][0], 'dmy');
            }

            if (isset($data->regrinfo['domain']['created'][0])) {
                $data->regrinfo['domain']['created'] = $this->getDate($data->regrinfo['domain']['created'][0], 'dmy');
            }

            $data->regrinfo['registered'] = 'yes';
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        return $data;
    }
}
