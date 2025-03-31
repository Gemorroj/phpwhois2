<?php

namespace PHPWhois2\Handler\Ip;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class ArinHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'OrgName:' => 'owner.organization',
            'CustName:' => 'owner.organization',
            'OrgId:' => 'owner.handle',
            'Address:' => 'owner.address.street.',
            'City:' => 'owner.address.city',
            'StateProv:' => 'owner.address.state',
            'PostalCode:' => 'owner.address.pcode',
            'Country:' => 'owner.address.country',
            'NetRange:' => 'network.inetnum',
            'NetName:' => 'network.name',
            'NetHandle:' => 'network.handle',
            'NetType:' => 'network.status',
            'NameServer:' => 'network.nserver.',
            'Comment:' => 'network.desc.',
            'RegDate:' => 'network.created',
            'Updated:' => 'network.changed',
            'ASHandle:' => 'network.handle',
            'ASName:' => 'network.name',
            'TechHandle:' => 'tech.handle',
            'TechName:' => 'tech.name',
            'TechPhone:' => 'tech.phone',
            'TechEmail:' => 'tech.email',
            'OrgAbuseName:' => 'abuse.name',
            'OrgAbuseHandle:' => 'abuse.handle',
            'OrgAbusePhone:' => 'abuse.phone',
            'OrgAbuseEmail:' => 'abuse.email.',
            'ReferralServer:' => 'rwhois',
        ];

        $r = $this->generic_parser_b($rawData, $items, 'ymd', false, true);
        if (isset($r['abuse']['email'])) {
            $r['abuse']['email'] = \implode(',', $r['abuse']['email']);
        }

        $data = new Data();
        $data->regrinfo = $r;
        $data->regyinfo = [];
        $data->rawData = $rawData;

        return $data;
    }
}
