<?php

namespace PHPWhois2\Handlers;

class IeHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'Domain:' => 'domain.name',
            'Registration Date:' => 'domain.created',
            'Renewal Date:' => 'domain.expires',
            'Nserver:' => 'domain.nserver.',
            'Admin-c:' => 'admin.handle',
            'Tech-c:' => 'tech.handle',
            'Domain Holder:' => 'owner.name',
        ];

        $reg = static::generic_parser_b($data_str['rawdata'], $items);

        if (isset($reg['domain']['descr'])) {
            $reg['owner']['organization'] = $reg['domain']['descr'][0];
            unset($reg['domain']['descr']);
        }

        $r = [
            'rawdata' => $data_str['rawdata'],
        ];

        $r['regrinfo'] = $reg;
        $r['regyinfo'] = [
            'referrer' => 'http://www.domainregistry.ie',
            'registrar' => 'IE Domain Registry',
        ];

        return $r;
    }
}
