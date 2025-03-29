<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class DirectnicHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant:',
            'admin' => 'Administrative Contact',
            'tech' => 'Technical Contact',
            'domain.name' => 'Domain Name:',
            'domain.sponsor' => 'Registration Service Provider:',
            'domain.nserver' => 'Domain servers in listed order:',
            'domain.changed' => 'Record last updated ',
            'domain.created' => 'Record created on ',
            'domain.expires' => 'Record expires on ',
            '' => 'By submitting a WHOIS query',
        ];

        return static::easyParser($data_str, $items, 'mdy', [], false, true);
    }
}
