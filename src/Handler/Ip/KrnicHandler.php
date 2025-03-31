<?php

namespace PHPWhois2\Handler\Ip;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class KrnicHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $blocks = [
            'owner1' => '[ Organization Information ]',
            'tech1' => '[ Technical Contact Information ]',
            'owner2' => '[ ISP Organization Information ]',
            'admin2' => '[ ISP IP Admin Contact Information ]',
            'tech2' => '[ ISP IP Tech Contact Information ]',
            'admin3' => '[ ISP IPv4 Admin Contact Information ]',
            'tech3' => '[ ISP IPv4 Tech Contact Information ]',
            'abuse' => '[ ISP Network Abuse Contact Information ]',
            'network.inetnum' => 'IPv4 Address       :',
            'network.name' => 'Network Name       :',
            'network.mnt-by' => 'Connect ISP Name   :',
            'network.created' => 'Registration Date  :',
        ];

        $items = [
            'Orgnization ID     :' => 'handle',
            'Org Name      :' => 'organization',
            'Org Name           :' => 'organization',
            'Name          :' => 'name',
            'Name               :' => 'name',
            'Org Address   :' => 'address.street',
            'Zip Code      :' => 'address.pcode',
            'State         :' => 'address.state',
            'Address            :' => 'address.street',
            'Zip Code           :' => 'address.pcode',
            'Phone         :' => 'phone',
            'Phone              :' => 'phone',
            'Fax           :' => 'fax',
            'E-Mail        :' => 'email',
            'E-Mail             :' => 'email',
        ];

        $b = $this->getBlocks($rawData, $blocks);

        $r = [];
        if (isset($b['network'])) {
            $r['network'] = $b['network'];
        }

        if (isset($b['owner1'])) {
            $r['owner'] = $this->generic_parser_b($b['owner1'], $items, 'Ymd', false);
        } elseif (isset($b['owner2'])) {
            $r['owner'] = $this->generic_parser_b($b['owner2'], $items, 'Ymd', false);
        }

        if (isset($b['admin2'])) {
            $r['admin'] = $this->generic_parser_b($b['admin2'], $items, 'Ymd', false);
        } elseif (isset($b['admin3'])) {
            $r['admin'] = $this->generic_parser_b($b['admin3'], $items, 'Ymd', false);
        }

        if (isset($b['tech1'])) {
            $r['tech'] = $this->generic_parser_b($b['tech1'], $items, 'Ymd', false);
        } elseif (isset($b['tech2'])) {
            $r['tech'] = $this->generic_parser_b($b['tech2'], $items, 'Ymd', false);
        } elseif (isset($b['tech3'])) {
            $r['tech'] = $this->generic_parser_b($b['tech3'], $items, 'Ymd', false);
        }
        if (isset($b['abuse'])) {
            $r['abuse'] = $this->generic_parser_b($b['abuse'], $items, 'Ymd', false);
        }

        $this->formatDates($r, 'Ymd');

        $data = new Data();
        $data->regrinfo = $r;
        $data->regyinfo = [
            'type' => 'ip',
            'registrar' => 'Korean Network Information Centre',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
