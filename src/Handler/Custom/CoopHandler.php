<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class CoopHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'owner' => 'Contact Type:            registrant',
            'admin' => 'Contact Type:            admin',
            'tech' => 'Contact Type:            tech',
            'billing' => 'Contact Type:            billing',
            'domain.name' => 'Domain Name:',
            'domain.handle' => 'Domain ID:',
            'domain.expires' => 'Expiry Date:',
            'domain.created' => 'Creation Date:',
            'domain.changed' => 'Updated Date:',
            'domain.status' => 'Domain Status:',
            'domain.sponsor' => 'Sponsoring registrar:',
            'domain.nserver.' => 'Host Name:',
        ];

        $translate = [
            'Contact ID:' => 'handle',
            'Name:' => 'name',
            'Organisation:' => 'organization',
            'Street 1:' => 'address.street.0',
            'Street 2:' => 'address.street.1',
            'Street 3:' => 'address.street.2',
            'City:' => 'address.city',
            'State/Province:' => 'address.state',
            'Postal code:' => 'address.pcode',
            'Country:' => 'address.country',
            'Voice:' => 'phone',
            'Fax:' => 'fax',
            'Email:' => 'email',
        ];

        $blocks = $this->getBlocks($rawData, $items);

        $data = new Data();
        $data->regrinfo = [];
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.nic.coop',
            'registrar' => '.coop registry',
        ] : [];
        $data->rawData = $rawData;

        if (isset($blocks['domain'])) {
            $data->regrinfo['domain'] = $this->formatDates($blocks['domain'], 'dmy');
            $data->regrinfo['registered'] = 'yes';

            if (isset($blocks['owner'])) {
                $data->regrinfo['owner'] = $this->generic_parser_b($blocks['owner'], $translate, 'dmy', false);

                if (isset($blocks['tech'])) {
                    $data->regrinfo['tech'] = $this->generic_parser_b($blocks['tech'], $translate, 'dmy', false);
                }

                if (isset($blocks['admin'])) {
                    $data->regrinfo['admin'] = $this->generic_parser_b($blocks['admin'], $translate, 'dmy', false);
                }

                if (isset($blocks['billing'])) {
                    $data->regrinfo['billing'] = $this->generic_parser_b($blocks['billing'], $translate, 'dmy', false);
                }
            } else {
                $data->regrinfo['owner'] = $this->generic_parser_b($rawData, $translate, 'dmy', false);
            }
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        return $data;
    }
}
