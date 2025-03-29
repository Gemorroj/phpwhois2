<?php

namespace PHPWhois2\Handlers;

/**
 * @TODO BUG
 * - nserver -> array
 * - ContactID in address
 */
class ItHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'domain.name' => 'Domain:',
            'domain.nserver' => 'Nameservers',
            'domain.status' => 'Status:',
            'domain.expires' => 'Expire Date:',
            'owner' => 'Registrant',
            'admin' => 'Admin Contact',
            'tech' => 'Technical Contacts',
            'registrar' => 'Registrar',
        ];

        $extra = [
            'address:' => 'address.',
            'contactid:' => 'handle',
            'organization:' => 'organization',
            'created:' => 'created',
            'last update:' => 'changed',
            'web:' => 'web',
        ];

        $r = [
            'regrinfo' => static::easyParser($data_str['rawdata'], $items, 'ymd', $extra),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'registrar' => 'IT-Nic',
                'referrer' => 'https://www.nic.it/',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        if (isset($r['regrinfo']['registrar'])) {
            $r['regrinfo']['domain']['registrar'] = $r['regrinfo']['registrar'];
            unset($r['regrinfo']['registrar']);
        }

        return $r;
    }
}
