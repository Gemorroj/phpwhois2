<?php

namespace PHPWhois2\Handlers;

class LtHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $translate = [
            'contact nic-hdl:' => 'handle',
            'contact name:' => 'name',
        ];

        $items = [
            'admin' => 'Contact type:      Admin',
            'tech' => 'Contact type:      Tech',
            'zone' => 'Contact type:      Zone',
            'owner.name' => 'Registrar:',
            'owner.email' => 'Registrar email:',
            'domain.status' => 'Status:',
            'domain.created' => 'Registered:',
            'domain.changed' => 'Last updated:',
            'domain.nserver.' => 'NS:',
            '' => '%',
        ];

        return [
            'regrinfo' => static::easyParser($data_str['rawdata'], $items, 'ymd', $translate),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'registrar' => 'DOMREG.LT',
                'referrer' => 'https://www.domreg.lt',
            ],
        ];
    }
}
