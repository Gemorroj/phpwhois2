<?php

namespace PHPWhois2\Handler\Ip;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class RipeHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'person' => 'name',
            'netname' => 'name',
            'descr' => 'desc',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
        ];

        $r = $this->generic_parser_a($rawData, $translate, $contacts, 'network');

        if (isset($r['network']['desc'])) {
            $r['owner']['organization'] = $r['network']['desc'];
            unset($r['network']['desc']);
        }

        if (isset($r['admin']['abuse-mailbox'])) {
            $r['abuse']['email'] = $r['admin']['abuse-mailbox'];
            unset($r['admin']['abuse-mailbox']);
        }

        if (isset($r['tech']['abuse-mailbox'])) {
            $r['abuse']['email'] = $r['tech']['abuse-mailbox'];
            unset($r['tech']['abuse-mailbox']);
        }

        // Clean mess
        if (isset($r['tech']['tech-c'])) {
            unset($r['tech']['tech-c']);
        }
        if (isset($r['tech']['admin-c'])) {
            unset($r['tech']['admin-c']);
        }
        if (isset($r['admin']['tech-c'])) {
            unset($r['admin']['tech-c']);
        }
        if (isset($r['admin']['admin-c'])) {
            unset($r['admin']['admin-c']);
        }

        $data = new Data();
        $data->regrinfo = $r;
        $data->regyinfo = [
            'type' => 'ip',
            'registrar' => 'RIPE Network Coordination Centre',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
