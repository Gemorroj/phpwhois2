<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class DkHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'Name' => 'name',
            'Address' => 'address.street',
            'City' => 'address.city',
            'Postalcode' => 'address.pcode',
            'Country' => 'address.country',
        ];

        $disclaimer = [];
        $blocks = $this->generic_parser_a_blocks($rawData, $translate, $disclaimer);

        $reg = [];
        if ($disclaimer && \is_array($disclaimer)) {
            $reg['disclaimer'] = $disclaimer;
        }

        if (empty($blocks) || !\is_array($blocks['main'])) {
            $reg['registered'] = 'no';
        } else {
            $r = $blocks['main'];
            $reg['registered'] = 'yes';

            $ownerHandlePos = \array_search('Registrant', $rawData, true) + 1;
            $ownerHandle = \trim(\substr(\strstr($rawData[$ownerHandlePos], ':'), 1));

            $adminHandlePos = \array_search('Administrator', $rawData, true) + 1;
            $adminHandle = \trim(\substr(\strstr($rawData[$adminHandlePos], ':'), 1));

            $contacts = [
                'owner' => $ownerHandle,
                'admin' => $adminHandle,
            ];

            foreach ($contacts as $key => $val) {
                $blk = \strtoupper(\strtok($val, ' '));
                if (isset($blocks[$blk])) {
                    $reg[$key] = $blocks[$blk];
                }
            }

            $reg['domain'] = $r;

            $this->formatDates($reg, 'Ymd');
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.dk-hostmaster.dk/',
            'registrar' => 'DK Hostmaster',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
