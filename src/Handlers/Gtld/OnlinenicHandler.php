<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class OnlinenicHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrator:',
            'tech' => 'Technical Contactor:',
            'billing' => 'Billing Contactor:',
            'domain.name' => 'Domain name:',
            'domain.name#' => 'Domain Name:',
            'domain.nserver' => 'Domain servers in listed order:',
            'domain.created' => 'Record created on ',
            'domain.expires' => 'Record expired on ',
            'domain.changed' => 'Record last updated at ',
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
            'province:' => '',
            ',province:' => '',
            ',country:' => 'address.country',
        ];

        $r = static::easyParser($data_str, $items, 'mdy', $extra, false, true);

        foreach ($r as $key => $part) {
            if (isset($part['email'])) {
                @[$email, $phone] = \explode(' ', $part['email']);
                $email = \str_replace('(', '', $email);
                $email = \str_replace(')', '', $email);
                $r[$key]['email'] = $email;
                if ('' != $phone) {
                    $r[$key]['phone'] = $phone;
                }
            }
        }

        return $r;
    }
}
