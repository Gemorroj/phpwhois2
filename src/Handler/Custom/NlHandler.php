<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class NlHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.name' => 'Domain name:',
            'domain.status' => 'Status:',
            'domain.nserver' => 'Domain nameservers:',
            'domain.created' => 'Date registered:',
            'domain.changed' => 'Record last updated:',
            'domain.sponsor' => 'Registrar:',
            'admin' => 'Administrative contact:',
            'tech' => 'Technical contact(s):',
        ];

        $data = new Data();
        $data->regrinfo = $this->getBlocks($rawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.domain-registry.nl',
            'registrar' => 'Stichting Internet Domeinregistratie NL',
        ] : [];
        $data->rawData = $rawData;

        if (!isset($data->regrinfo['domain']['status'])) {
            $data->regrinfo['registered'] = 'no';

            return $data;
        }

        if (isset($data->regrinfo['tech'])) {
            $data->regrinfo['tech'] = $this->getContact($data->regrinfo['tech']);
        }
        if (isset($data->regrinfo['zone'])) {
            $data->regrinfo['zone'] = $this->getContact($data->regrinfo['zone']);
        }
        if (isset($data->regrinfo['admin'])) {
            $data->regrinfo['admin'] = $this->getContact($data->regrinfo['admin']);
        }
        if (isset($data->regrinfo['owner'])) {
            $data->regrinfo['owner'] = $this->getContact($data->regrinfo['owner']);
        }

        $data->regrinfo['registered'] = 'yes';

        $this->formatDates($data->regrinfo, 'dmy');

        return $data;
    }

    protected function getContact($array, $extraItems = [], $hasOrg = false): array
    {
        $r = parent::getContact($array, $extraItems, $hasOrg);

        if (isset($r['name']) && \preg_match('/^[A-Z0-9]+-[A-Z0-9]+$/', $r['name'])) {
            $r['handle'] = $r['name'];
            $r['name'] = \array_shift($r['address']);
        }

        return $r;
    }
}
