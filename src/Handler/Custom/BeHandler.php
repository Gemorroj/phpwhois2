<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class BeHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.name' => 'Domain:',
            'domain.status' => 'Status:',
            'domain.nserver' => 'Nameservers:',
            'domain.created' => 'Registered:',
            'owner' => 'Licensee:',
            'admin' => 'Onsite Contacts:',
            'tech' => 'Registrar Technical Contacts:',
            'agent' => 'Registrar:',
            'agent.uri' => 'Website:',
        ];

        $trans = [
            'company name2:' => '',
        ];

        $filteredRawData = \array_filter($rawData);

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($filteredRawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($filteredRawData) ? [
            'referrer' => 'https://www.domain-registry.nl',
            'registrar' => 'DNS Belgium',
        ] : [];
        $data->rawData = $rawData;

        $domainStatus = $data->regrinfo['domain']['status'];
        if ('REGISTERED' === $domainStatus || 'NOT AVAILABLE' === $domainStatus) {
            $data->regrinfo['registered'] = 'yes';

            $data->regrinfo = $this->getContacts($data->regrinfo, $trans);

            if (isset($data->regrinfo['agent'])) {
                $sponsor = $this->getContact($data->regrinfo['agent'], $trans);
                unset($data->regrinfo['agent']);
                $data->regrinfo['domain']['sponsor'] = $sponsor;
            }

            $data->regrinfo = $this->formatDates($data->regrinfo, '-mdy');
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        return $data;
    }
}
