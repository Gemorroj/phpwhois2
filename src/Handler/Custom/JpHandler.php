<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class JpHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items1 = [
            '[State]' => 'domain.status',
            '[Status]' => 'domain.status',
            '[Registered Date]' => 'domain.created',
            '[Created on]' => 'domain.created',
            '[Expires on]' => 'domain.expires',
            '[Last Updated]' => 'domain.changed',
            '[Last Update]' => 'domain.changed',
            '[Organization]' => 'owner.organization',
            '[Name]' => 'owner.name',
            '[Email]' => 'owner.email',
            '[Postal code]' => 'owner.address.pcode',
            '[Postal Address]' => 'owner.address.street',
            '[Phone]' => 'owner.phone',
            '[Fax]' => 'owner.fax',
            '[Administrative Contact]' => 'admin.handle',
            '[Technical Contact]' => 'tech.handle',
            '[Name Server]' => 'domain.nserver.',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items1, 'ymd');
        $data->regyinfo = [
            'referrer' => 'http://www.jprs.jp',
            'registrar' => 'Japan Registry Services',
        ];
        $data->rawData = $rawData;

        $items2 = [
            'a. [JPNIC Handle]' => 'handle',
            'c. [Last, First]' => 'name',
            'd. [E-Mail]' => 'email',
            'g. [Organization]' => 'organization',
            'o. [TEL]' => 'phone',
            'p. [FAX]' => 'fax',
            '[Last Update]' => 'changed',
        ];

        // $this->whoisClient->queryParams->clear();
        $this->whoisClient->queryParams->args = null;
        $this->whoisClient->queryParams->server = 'jp.whois-servers.net';

        if (!empty($data->regrinfo['admin']['handle'])) {
            $rwdata = $this->whoisClient->getRawData('CONTACT '.$data->regrinfo['admin']['handle'].'/e');
            \array_push($data->rawData, "\n\n", '-----------------------------------', "\n\n", ...$rwdata);

            $data->regrinfo['admin'] = $this->generic_parser_b($rwdata, $items2, 'ymd', false);
            $this->whoisClient->makeWhoisInfo($data);
        }

        if (!empty($data->regrinfo['tech']['handle'])) {
            if (!empty($data->regrinfo['admin']['handle']) && $data->regrinfo['admin']['handle'] === $data->regrinfo['tech']['handle']) {
                $data->regrinfo['tech'] = $data->regrinfo['admin'];
            } else {
                $rwdata = $this->whoisClient->getRawData('CONTACT '.$data->regrinfo['tech']['handle'].'/e');
                \array_push($data->rawData, "\n\n", '-----------------------------------', "\n\n", ...$rwdata);

                $data->regrinfo['tech'] = $this->generic_parser_b($rwdata, $items2, 'ymd', false);
                $this->whoisClient->makeWhoisInfo($data);
            }
        }

        return $data;
    }
}
