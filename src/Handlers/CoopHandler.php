<?php

namespace PHPWhois2\Handlers;

class CoopHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
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

        $blocks = static::getBlocks($data_str['rawdata'], $items);

        $r = [
            'regrinfo' => [],
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.nic.coop',
                'registrar' => '.coop registry',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        if (isset($blocks['domain'])) {
            $r['regrinfo']['domain'] = static::formatDates($blocks['domain'], 'dmy');
            $r['regrinfo']['registered'] = 'yes';

            if (isset($blocks['owner'])) {
                $r['regrinfo']['owner'] = static::generic_parser_b($blocks['owner'], $translate, 'dmy', false);

                if (isset($blocks['tech'])) {
                    $r['regrinfo']['tech'] = static::generic_parser_b($blocks['tech'], $translate, 'dmy', false);
                }

                if (isset($blocks['admin'])) {
                    $r['regrinfo']['admin'] = static::generic_parser_b($blocks['admin'], $translate, 'dmy', false);
                }

                if (isset($blocks['billing'])) {
                    $r['regrinfo']['billing'] = static::generic_parser_b($blocks['billing'], $translate, 'dmy', false);
                }
            } else {
                $r['regrinfo']['owner'] = static::generic_parser_b($data_str['rawdata'], $translate, 'dmy', false);
            }
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        return $r;
    }
}
