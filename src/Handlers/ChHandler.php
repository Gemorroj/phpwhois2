<?php

namespace PHPWhois2\Handlers;

class ChHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Holder of domain name:',
            'domain.name' => 'Domain name:',
            'domain.created' => 'Date of last registration:',
            'domain.changed' => 'Date of last modification:',
            'tech' => 'Technical contact:',
            'domain.nserver' => 'Name servers:',
            'domain.dnssec' => 'DNSSEC:',
        ];

        $trans = [
            'contractual language:' => 'language',
        ];

        $r = [];
        $r['regrinfo'] = static::getBlocks($data_str['rawdata'], $items);

        if (!empty($r['regrinfo']['domain']['name'])) {
            $r['regrinfo'] = static::getContacts($r['regrinfo'], $trans);

            $r['regrinfo']['domain']['name'] = $r['regrinfo']['domain']['name'][0];

            if (isset($r['regrinfo']['domain']['changed'][0])) {
                $r['regrinfo']['domain']['changed'] = static::getDate($r['regrinfo']['domain']['changed'][0], 'dmy');
            }

            if (isset($r['regrinfo']['domain']['created'][0])) {
                $r['regrinfo']['domain']['created'] = static::getDate($r['regrinfo']['domain']['created'][0], 'dmy');
            }

            $r['regrinfo']['registered'] = 'yes';
        } else {
            $r = [];
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo'] = [
            'referrer' => 'https://www.nic.ch/',
            'registrar' => 'SWITCH Domain Name Registration',
        ];

        return $r;
    }
}
