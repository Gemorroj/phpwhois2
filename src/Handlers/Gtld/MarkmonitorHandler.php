<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class MarkmonitorHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact, Zone Contact:',
            'domain.name' => 'Domain Name:',
            'domain.sponsor' => 'Registrar Name:',
            'domain.nserver' => 'Domain servers in listed order:',
            'domain.created' => 'Created on..............:',
            'domain.expires' => 'Expires on..............:',
            'domain.changed' => 'Record last updated on..:',
        ];

        $r = static::easyParser($data_str, $items, 'dmy', [], false, true);

        if (isset($r['domain']['sponsor']) && \is_array($r['domain']['sponsor'])) {
            $r['domain']['sponsor'] = $r['domain']['sponsor'][0];
        }

        foreach ($r as $key => $part) {
            if (isset($part['address'])) {
                $r[$key]['organization'] = \array_shift($r[$key]['address']);
                $r[$key]['address']['country'] = \array_pop($r[$key]['address']);
            }
        }

        return $r;
    }
}
