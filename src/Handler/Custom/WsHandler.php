<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class WsHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'Domain Name:' => 'domain.name',
            'Creation Date:' => 'domain.created',
            'Updated Date:' => 'domain.changed',
            'Registrar Registration Expiration Date:' => 'domain.expires',
            'Registrar:' => 'domain.sponsor',
            'WHOIS Server:' => 'rwhois',
            'Domain Status:' => 'domain.status.',
            'Registrant Name:' => 'owner.name',
            'Registrant Organization:' => 'owner.organization',
            'Registrant Street:' => 'owner.address.address.0',
            'Registrant City:' => 'owner.address.city',
            'Registrant State/Province:' => 'owner.address.state',
            'Registrant Postal Code:' => 'owner.address.pcode',
            'Registrant Country:' => 'owner.address.country',
            'Registrant Phone:' => 'owner.phone',
            'Registrant Fax:' => 'owner.fax',
            'Registrant Email:' => 'owner.email',
            'Domain Created:' => 'domain.created',
            'Admin Name:' => 'admin.name',
            'Domain Last Updated:' => 'domain.changed',
            'Admin Organization:' => 'admin.organization',
            'Registrar Name:' => 'domain.sponsor',
            'Admin Street:' => 'admin.address.address.0',
            'Current Nameservers:' => 'domain.nserver.',
            'Admin City:' => 'admin.address.city',
            'Administrative Contact Email:' => 'admin.email',
            'Admin State/Province:' => 'admin.address.state',
            'Administrative Contact Telephone:' => 'admin.phone',
            'Admin Postal Code:' => 'admin.address.pcode',
            'Registrar Whois:' => 'rwhois',
            'Admin Country:' => 'admin.address.country',
            'Admin Phone:' => 'admin.phone',
            'Admin Fax:' => 'admin.fax',
            'Admin Email:' => 'admin.email',
            'Tech Name:' => 'tech.name',
            'Tech Organization:' => 'tech.organization',
            'Tech Street:' => 'tech.address.address.0',
            'Tech City:' => 'tech.address.city',
            'Tech State/Province:' => 'tech.address.state',
            'Tech Postal Code:' => 'tech.address.pcode',
            'Tech Country:' => 'tech.address.country',
            'Tech Phone:' => 'tech.phone',
            'Tech Fax:' => 'tech.fax',
            'Tech Email:' => 'tech.email',
            'Name Server:' => 'domain.nserver.',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items, 'ymd');
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.samoanic.ws',
            'registrar' => 'Samoa Nic',
        ] : [];
        $data->rawData = $rawData;

        if (!empty($data->regrinfo['domain']['name'])) {
            $data->regrinfo['registered'] = 'yes';
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        return $data;
    }
}
