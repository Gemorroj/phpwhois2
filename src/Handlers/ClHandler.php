<?php

namespace PHPWhois2\Handlers;

class ClHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'admin' => '(Administrative Contact)',
            'tech' => 'Contacto Técnico (Technical Contact):',
            // 'domain.nserver' => 'Servidores de nombre (Domain servers):',
            'domain.nserver' => 'Name server:',
            'domain.changed' => '(Database last updated on):',
            'domain.created' => 'Creation date:',
            'domain.expires' => 'Expiration date:',
        ];

        $trans = [
            'organización:' => 'organization',
            'nombre      :' => 'name',
        ];

        $rawData = $this->removeBlankLines($data_str['rawdata']);
        $r = [
            'rawdata' => $data_str['rawdata'],
            'regrinfo' => $this->easyParser($rawData, $items, 'd-m-y', $trans),
        ];

        if (!isset($r['regrinfo']['domain']['name'])) {
            $r['regrinfo']['domain']['name'] = $query;
        }

        $r['regyinfo'] = [
            'referrer' => 'http://www.nic.cl',
            'registrar' => 'NIC Chile',
        ];

        return $r;
    }
}
