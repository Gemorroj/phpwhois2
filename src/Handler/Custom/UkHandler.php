<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class UkHandler extends AbstractHandler
{
    private const ITEMS = [
        'owner.organization' => 'Registrant:',
        'owner.address' => "Registrant's address:",
        'owner.type' => 'Registrant type:',
        'domain.created' => 'Registered on:',
        'domain.changed' => 'Last updated:',
        'domain.expires' => 'Expiry date:',
        'domain.nserver' => 'Name servers:',
        'domain.sponsor' => 'Registrar:',
        'domain.status' => 'Registration status:',
        'domain.dnssec' => 'DNSSEC:',
        '' => 'WHOIS lookup made at',
        'disclaimer' => '--',
    ];

    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $rawDataFiltered = \array_filter($rawData);

        $data = new Data();
        $data->regrinfo = $this->getBlocks($rawDataFiltered, static::ITEMS);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.nominet.org.uk',
            'registrar' => 'Nominet UK',
        ] : [];
        $data->rawData = $rawData;

        if (isset($data->regrinfo['owner'])) {
            $data->regrinfo['owner']['organization'] = $data->regrinfo['owner']['organization'][0];
            $data->regrinfo['domain']['sponsor'] = $data->regrinfo['domain']['sponsor'][0];
            $data->regrinfo['registered'] = 'yes';
        } elseif (isset($data->regrinfo['domain']['status'][0]) && \strpos($query, '.co.uk')) {
            if ('Registered until expiry date.' === $data->regrinfo['domain']['status'][0]) {
                $data->regrinfo['registered'] = 'yes';
            }
        } elseif (\strpos($rawData[1], 'Error for ')) {
            $data->regrinfo['registered'] = 'yes';
            $data->regrinfo['domain']['status'] = 'invalid';
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        $this->formatDates($data->regrinfo, 'dmy');

        return $data;
    }
}
