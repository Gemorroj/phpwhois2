<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class FabulousHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Domain '.$query.':',
            'admin' => 'Administrative contact:',
            'tech' => 'Technical contact:',
            'billing' => 'Billing contact:',
            '' => 'Record dates:',
        ];

        $r = static::easyParser($data_str, $items, 'mdy', [], false, true);

        if (!isset($r['tech'])) {
            $r['tech'] = $r['billing'];
        }

        if (!isset($r['admin'])) {
            $r['admin'] = $r['tech'];
        }

        return $r;
    }
}
