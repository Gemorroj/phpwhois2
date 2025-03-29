<?php

namespace PHPWhois2\Handlers;

class AtHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'person' => 'name',
            'personname' => 'name',
            'street address' => 'address.street',
            'city' => 'address.city',
            'postal code' => 'address.pcode',
            'country' => 'address.country',
            // 'domain'         => 'domain.name',
        ];

        $contacts = [
            'registrant' => 'owner',
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'billing-c' => 'billing',
            'zone-c' => 'zone',
        ];

        $reg = static::generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

        if (isset($reg['domain']['remarks'])) {
            unset($reg['domain']['remarks']);
        }

        if (isset($reg['domain']['descr'])) {
            foreach ($reg['domain']['descr'] as $key => $val) {
                $v = \trim(\substr(\strstr($val, ':'), 1));
                if (\str_contains($val, '[organization]:')) {
                    $reg['owner']['organization'] = $v;
                    continue;
                }
                if (\str_contains($val, '[phone]:')) {
                    $reg['owner']['phone'] = $v;
                    continue;
                }
                if (\str_contains($val, '[fax-no]:')) {
                    $reg['owner']['fax'] = $v;
                    continue;
                }
                if (\str_contains($val, '[e-mail]:')) {
                    $reg['owner']['email'] = $v;
                    continue;
                }

                $reg['owner']['address'][$key] = $v;
            }

            unset($reg['domain']['descr']);
        }

        $r = [
            'regrinfo' => $reg,
            'regyinfo' => [
                'referrer' => 'http://www.nic.at',
                'registrar' => 'NIC-AT',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        return $r;
    }
}
