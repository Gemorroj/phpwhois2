<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class IanaHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'admin' => 'contact:      administrative',
            'tech' => 'contact:      technical',
            'domain.nserver.' => 'nserver:',
            'domain.created' => 'created:',
            'domain.changed' => 'changed:',
            'domain.source' => 'source:',
            'domain.name' => 'domain:',
            'disclaimer.' => '% ',
        ];

        return static::easyParser($data_str, $items, 'Ymd', [], false, false, 'owner');
    }
}
