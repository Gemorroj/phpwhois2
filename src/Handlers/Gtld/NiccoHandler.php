<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class NiccoHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Holder Contact',
            'admin' => 'Admin Contact',
            'tech' => 'Tech. Contact',
            'domain.nserver.' => 'Nameservers',
            'domain.created' => 'Creation Date:',
            'domain.expires' => 'Expiration Date:',
        ];

        $translate = [
            'city:' => 'address.city',
            'org. name:' => 'organization',
            'address1:' => 'address.street.',
            'address2:' => 'address.street.',
            'state:' => 'address.state',
            'postal code:' => 'address.zip',
        ];

        $r = static::getBlocks($data_str, $items, true);
        $r['owner'] = static::getContact($r['owner'], $translate);
        $r['admin'] = static::getContact($r['admin'], $translate, true);
        $r['tech'] = static::getContact($r['tech'], $translate, true);

        return static::formatDates($r, 'dmy');
    }
}
