<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class NamekingHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant',
            'admin' => 'Admin Contact',
            'tech' => 'Tech Contact',
            'billing' => 'Billing Contact',
            'domain.sponsor' => 'Registration Provided By:',
            'domain.created' => 'Creation Date:',
            'domain.expires' => 'Expiration Date:',
        ];

        $extra = [
            'tel--' => 'phone',
            'tel:' => 'phone',
            'tel --:' => 'phone',
            'email-:' => 'email',
            'email:' => 'email',
            'mail:' => 'email',
            'name--' => 'name',
            'org:' => 'organization',
            'zipcode:' => 'address.pcode',
            'postcode:' => 'address.pcode',
            'address:' => 'address.street',
            'city:' => 'address.city',
            'province:' => 'address.city.',
            ',province:' => '',
            ',country:' => 'address.country',
            'organization:' => 'organization',
            'city, province, post code:' => 'address.city',
        ];

        return static::easyParser($data_str, $items, 'mdy', $extra, false, true);
    }
}
