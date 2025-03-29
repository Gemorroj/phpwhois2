<?php

namespace PHPWhois2\Handlers;

class AmHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'domain.name' => 'Domain name:',
            'domain.created' => 'Registered:',
            'domain.changed' => 'Last modified:',
            'domain.nserver' => 'DNS servers:',
            'domain.status' => 'Status:',
            'tech' => 'Technical contact:',
            'admin' => 'Administrative contact:',
        ];

        $rawData = $this->removeBlankLines($data_str['rawdata']);
        $r = [
            'regrinfo' => $this->getBlocks($rawData, $items),
            'rawdata' => $data_str['rawdata'],
        ];

        if (!empty($r['regrinfo']['domain']['name'])) {
            $r['regrinfo'] = $this->getContacts($r['regrinfo']);
            $r['regrinfo']['registered'] = 'yes';
        } else {
            $r = [];
            $r['regrinfo']['registered'] = 'no';
        }

        $r['regyinfo'] = [
            'referrer' => 'http://www.isoc.am',
            'registrar' => 'ISOCAM',
        ];

        return $r;
    }
}
