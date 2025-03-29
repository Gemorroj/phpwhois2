<?php

namespace PHPWhois2\Handlers;

class FjHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'domain.name' => 'Domain name:',
            'domain.status' => 'Status:',
            'domain.expires' => 'Expires:',
            'domain.nserver' => 'Domain servers:',
        ];

        $r = [
            'regrinfo' => static::getBlocks($data_str['rawdata'], $items),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.domains.fj',
                'registrar' => 'FJ Domain Name Registry',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        if (!empty($r['regrinfo']['domain']['status'])) {
            $r['regrinfo'] = static::getContacts($r['regrinfo']);

            \date_default_timezone_set('Pacific/Fiji');

            if (isset($r['regrinfo']['domain']['expires'])) {
                $r['regrinfo']['domain']['expires'] = \strftime(
                    '%Y-%m-%d',
                    \strtotime($r['regrinfo']['domain']['expires'])
                );
            }

            $r['regrinfo']['registered'] = 'yes';
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        return $r;
    }
}
