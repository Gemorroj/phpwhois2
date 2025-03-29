<?php

namespace PHPWhois2\Handlers\Ip;

use PHPWhois2\Handlers\AbstractHandler;

class ArinHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
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

        $r = static::generic_parser_b($data_str, $items, 'ymd', false, true);

        if (isset($r['abuse']['email'])) {
            $r['abuse']['email'] = \implode(',', $r['abuse']['email']);
        }

        return ['regrinfo' => $r];
    }
}
