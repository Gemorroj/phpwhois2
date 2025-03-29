<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class SchlundHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'created:' => 'domain.created',
            'last-changed:' => 'domain.changed',
            'status:' => 'domain.status',
            'registrant-firstname:' => 'owner.name.first',
            'registrant-lastname:' => 'owner.name.last',
            'registrant-organization:' => 'owner.organization',
            'registrant-street1:' => 'owner.address.street.',
            'registrant-street2:' => 'owner.address.street.',
            'registrant-pcode:' => 'owner.address.pcode',
            'registrant-city:' => 'owner.address.city',
            'registrant-ccode:' => 'owner.address.country',
            'registrant-phone:' => 'owner.phone',
            'registrant-email:' => 'owner.email',
            'admin-c-firstname:' => 'admin.name.first',
            'admin-c-lastname:' => 'admin.name.last',
            'admin-c-organization:' => 'admin.organization',
            'admin-c-street1:' => 'admin.address.street.',
            'admin-c-street2:' => 'admin.address.street.',
            'admin-c-pcode:' => 'admin.address.pcode',
            'admin-c-city:' => 'admin.address.city',
            'admin-c-ccode:' => 'admin.address.country',
            'admin-c-phone:' => 'admin.phone',
            'admin-c-email:' => 'admin.email',
            'tech-c-firstname:' => 'tech.name.first',
            'tech-c-lastname:' => 'tech.name.last',
            'tech-c-organization:' => 'tech.organization',
            'tech-c-street1:' => 'tech.address.street.',
            'tech-c-street2:' => 'tech.address.street.',
            'tech-c-pcode:' => 'tech.address.pcode',
            'tech-c-city:' => 'tech.address.city',
            'tech-c-ccode:' => 'tech.address.country',
            'tech-c-phone:' => 'tech.phone',
            'tech-c-email:' => 'tech.email',
            'bill-c-firstname:' => 'billing.name.first',
            'bill-c-lastname:' => 'billing.name.last',
            'bill-c-organization:' => 'billing.organization',
            'bill-c-street1:' => 'billing.address.street.',
            'bill-c-street2:' => 'billing.address.street.',
            'bill-c-pcode:' => 'billing.address.pcode',
            'bill-c-city:' => 'billing.address.city',
            'bill-c-ccode:' => 'billing.address.country',
            'bill-c-phone:' => 'billing.phone',
            'bill-c-email:' => 'billing.email',
        ];

        return static::generic_parser_b($data_str, $items);
    }
}
