<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class NameintelHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant Contact:',
            'admin' => 'Administrative Contact:',
            'tech' => 'Technical Contact',
            'domain.name' => 'Domain Name:',
            'domain.status' => 'Status:',
            'domain.nserver' => 'Name Server:',
            'domain.created' => 'Creation Date:',
            'domain.expires' => 'Expiration Date:',
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
