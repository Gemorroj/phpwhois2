<?php

namespace PHPWhois2\Handlers;

class BeHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'domain.name' => 'Domain:',
            'domain.status' => 'Status:',
            'domain.nserver' => 'Nameservers:',
            'domain.created' => 'Registered:',
            'owner' => 'Licensee:',
            'admin' => 'Onsite Contacts:',
            'tech' => 'Registrar Technical Contacts:',
            'agent' => 'Registrar:',
            'agent.uri' => 'Website:',
        ];

        $trans = [
            'company name2:' => '',
        ];

        $rawData = $this->removeBlankLines($data_str['rawdata']);

        $r = [
            'regrinfo' => static::getBlocks($rawData, $items),
            'regyinfo' => $this->parseRegistryInfo($rawData) ?? [
                'referrer' => 'https://www.domain-registry.nl',
                'registrar' => 'DNS Belgium',
            ],
            'rawdata' => $data_str['rawdata'],
        ];

        $domainStatus = $r['regrinfo']['domain']['status'];
        if ('REGISTERED' === $domainStatus || 'NOT AVAILABLE' === $domainStatus) {
            $r['regrinfo']['registered'] = 'yes';

            $r['regrinfo'] = static::getContacts($r['regrinfo'], $trans);

            if (isset($r['regrinfo']['agent'])) {
                $sponsor = $this->getContact($r['regrinfo']['agent'], $trans);
                unset($r['regrinfo']['agent']);
                $r['regrinfo']['domain']['sponsor'] = $sponsor;
            }

            $r = static::formatDates($r, '-mdy');
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        return $r;
    }
}
