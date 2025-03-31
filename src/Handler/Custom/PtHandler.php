<?php

namespace PHPWhois2\Handler\Custom;

/*
TODO:
   - whois - converter para http://domaininfo.com/idn_conversion.asp punnycode antes de efectuar a pesquisa
   - o punnycode deveria fazer parte dos resultados fazer parte dos resultados!
*/

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class PtHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.name' => ' / Domain Name:',
            'domain.created' => 'Data de registo / Creation Date (dd/mm/yyyy):',
            'domain.nserver.' => 'Nameserver:',
            'domain.status' => 'Estado / Status:',
            'owner' => 'Titular / Registrant',
            'billing' => 'Entidade Gestora / Billing Contact',
            'admin' => 'Responsável Administrativo / Admin Contact',
            'tech' => 'Responsável Técnico / Tech Contact',
            '#' => 'Nameserver Information',
        ];

        $data = new Data();
        $data->regrinfo = $this->getBlocks($rawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'registrar' => 'FCCN',
            'referrer' => 'https://www.fccn.pt',
        ] : [];
        $data->rawData = $rawData;

        if (empty($data->regrinfo['domain']['name'])) {
            $data->regrinfo['registered'] = 'no';

            return $data;
        }

        $data->regrinfo['domain']['created'] = $this->getDate($data->regrinfo['domain']['created'], 'dmy');

        if ('ACTIVE' === $data->regrinfo['domain']['status']) {
            $data->regrinfo = $this->getContacts($data->regrinfo);
            $data->regrinfo['registered'] = 'yes';
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        return $data;
    }
}
