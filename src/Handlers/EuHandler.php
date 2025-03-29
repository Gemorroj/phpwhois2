<?php

namespace PHPWhois2\Handlers;

class EuHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'domain.name' => 'Domain:',
            'domain.status' => 'Status:',
            'domain.nserver' => 'Name servers:',
            'domain.created' => 'Registered:',
            'domain.registrar' => 'Registrar:',
            'tech' => 'Registrar Technical Contacts:',
            'owner' => 'Registrant:',
            '' => 'Please visit',
        ];

        $extra = [
            'organisation:' => 'organization',
            'website:' => 'url',
        ];

        $r = [
            'regrinfo' => static::getBlocks($data_str['rawdata'], $items),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.eurid.eu',
                'registrar' => 'EURID',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        if (!empty($r['regrinfo']['domain']['status'])) {
            switch ($r['regrinfo']['domain']['status']) {
                case 'FREE':
                case 'AVAILABLE':
                    $r['regrinfo']['registered'] = 'no';
                    break;

                case 'APPLICATION PENDING':
                    $r['regrinfo']['registered'] = 'pending';
                    break;

                default:
                    $r['regrinfo']['registered'] = 'unknown';
            }
        } else {
            $r['regrinfo']['registered'] = 'yes';
        }

        if (isset($r['regrinfo']['tech'])) {
            $r['regrinfo']['tech'] = static::getContact($r['regrinfo']['tech'], $extra);
        }

        if (isset($r['regrinfo']['domain']['registrar'])) {
            $r['regrinfo']['domain']['registrar'] = static::getContact($r['regrinfo']['domain']['registrar'], $extra);
        }

        return $r;
    }
}
