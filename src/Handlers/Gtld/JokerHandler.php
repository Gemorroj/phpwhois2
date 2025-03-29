<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class JokerHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $translate = [
            'contact-hdl' => 'handle',
            'modified' => 'changed',
            'reseller' => 'sponsor',
            'address' => 'address.street',
            'postal-code' => 'address.pcode',
            'city' => 'address.city',
            'state' => 'address.state',
            'country' => 'address.country',
            'person' => 'name',
            'domain' => 'name',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'billing-c' => 'billing',
        ];

        $items = [
            'owner' => 'name',
            'organization' => 'organization',
            'email' => 'email',
            'phone' => 'phone',
            'address' => 'address',
        ];

        $r = static::generic_parser_a($data_str, $translate, $contacts, 'domain', 'Ymd');

        foreach ($items as $tag => $convert) {
            if (isset($r['domain'][$tag])) {
                $r['owner'][$convert] = $r['domain'][$tag];
                unset($r['domain'][$tag]);
            }
        }

        return $r;
    }
}
