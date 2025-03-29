<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class RrpproxyHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'created-date:' => 'domain.created',
            'updated-date:' => 'domain.changed',
            'registration-expiration-date:' => 'domain.expires',
            'RSP:' => 'domain.sponsor',
            'URL:' => 'domain.referrer',
            'owner-nom.contact:' => 'owner.handle',
            'owner-fname:' => 'owner.name.first',
            'owner-lname:' => 'owner.name.last',
            'owner-organization:' => 'owner.organization',
            'owner-street:' => 'owner.address.street',
            'owner-city:' => 'owner.address.city',
            'owner-zip:' => 'owner.address.pcode',
            'owner-country:' => 'owner.address.country',
            'owner-phone:' => 'owner.phone',
            'owner-fax:' => 'owner.fax',
            'owner-email:' => 'owner.email',
            'admin-nom.contact:' => 'admin.handle',
            'admin-fname:' => 'admin.name.first',
            'admin-lname:' => 'admin.name.last',
            'admin-organization:' => 'admin.organization',
            'admin-street:' => 'admin.address.street',
            'admin-city:' => 'admin.address.city',
            'admin-zip:' => 'admin.address.pcode',
            'admin-country:' => 'admin.address.country',
            'admin-phone:' => 'admin.phone',
            'admin-fax:' => 'admin.fax',
            'admin-email:' => 'admin.email',
            'tech-nom.contact:' => 'tech.handle',
            'tech-fname:' => 'tech.name.first',
            'tech-lname:' => 'tech.name.last',
            'tech-organization:' => 'tech.organization',
            'tech-street:' => 'tech.address.street',
            'tech-city:' => 'tech.address.city',
            'tech-zip:' => 'tech.address.pcode',
            'tech-country:' => 'tech.address.country',
            'tech-phone:' => 'tech.phone',
            'tech-fax:' => 'tech.fax',
            'tech-email:' => 'tech.email',
            'billing-nom.contact:' => 'billing.handle',
            'billing-fname:' => 'billing.name.first',
            'billing-lname:' => 'billing.name.last',
            'billing-organization:' => 'billing.organization',
            'billing-street:' => 'billing.address.street',
            'billing-city:' => 'billing.address.city',
            'billing-zip:' => 'billing.address.pcode',
            'billing-country:' => 'billing.address.country',
            'billing-phone:' => 'billing.phone',
            'billing-fax:' => 'billing.fax',
            'billing-email:' => 'billing.email',
        ];

        return static::generic_parser_b($data_str, $items);
    }
}
