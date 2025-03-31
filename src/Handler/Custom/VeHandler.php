<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class VeHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'owner' => 'Titular:',
            'domain.name' => 'Nombre de Dominio:',
            'admin' => 'Contacto Administrativo',
            'tech' => 'Contacto Tecnico',
            'billing' => 'Contacto de Cobranza:',
            'domain.created' => 'Fecha de Creacion:',
            'domain.changed' => 'Ultima Actualizacion:',
            'domain.expires' => 'Fecha de Vencimiento:',
            'domain.status' => 'Estatus del dominio:',
            'domain.nserver' => 'Servidor(es) de Nombres de Dominio',
        ];

        $data = new Data();
        $data->regrinfo = $this->getBlocks($rawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://registro.nic.ve',
            'registrar' => 'NIC-Venezuela - CNTI',
        ] : [];
        $data->rawData = $rawData;

        if (!isset($data->regrinfo['domain']['created']) || \is_array($data->regrinfo['domain']['created'])) {
            $data->regrinfo = ['registered' => 'no'];

            return $data;
        }

        $dns = [];
        foreach ($data->regrinfo['domain']['nserver'] as $nserv) {
            if ('-' === $nserv[0]) {
                $dns[] = $nserv;
            }
        }
        $data->regrinfo['domain']['nserver'] = $dns;

        $data->regrinfo = $this->getContacts($data->regrinfo);

        return $data;
    }
}
